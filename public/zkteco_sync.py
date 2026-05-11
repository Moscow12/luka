#!/usr/bin/env python3
"""
ZKTeco Fingerprint Device Sync Script
This script connects to a ZKTeco device, retrieves users and attendance logs,
and outputs JSON data for Laravel to process.

Usage: python3 zkteco_sync.py <device_ip> <device_port> [days_back]
"""

import sys
import json
import signal
from datetime import datetime, timedelta

def output_json(data):
    """Output JSON to stdout"""
    print(json.dumps(data))
    sys.exit(0 if data.get('success', False) else 1)

def output_error(message):
    """Output error JSON"""
    output_json({
        "success": False,
        "error": message,
        "users": [],
        "attendance": []
    })

def timeout_handler(signum, frame):
    """Handle timeout signal"""
    output_error("Operation timed out - device may be slow or have too many records")

# Check for pyzk library first
try:
    from zk import ZK
    PYZK_AVAILABLE = True
except ImportError:
    PYZK_AVAILABLE = False

def sync_device(ip, port, days_back=7):
    """Connect to ZKTeco device and retrieve data"""

    if not PYZK_AVAILABLE:
        output_error("pyzk library not installed. Install with: pip3 install pyzk")
        return

    # Set up timeout handler (180 seconds max for entire operation)
    signal.signal(signal.SIGALRM, timeout_handler)
    signal.alarm(180)

    zk = None
    conn = None
    try:
        # Create ZK connection with shorter timeout
        # Try TCP first, then UDP if it fails
        zk = ZK(ip, port=int(port), timeout=15, password=0, force_udp=False, ommit_ping=False)
        try:
            conn = zk.connect()
        except Exception as tcp_error:
            # If TCP fails, try UDP
            zk = ZK(ip, port=int(port), timeout=15, password=0, force_udp=True, ommit_ping=False)
            try:
                conn = zk.connect()
            except Exception as udp_error:
                output_error(f"Connection failed (TCP: {str(tcp_error)[:50]}, UDP: {str(udp_error)[:50]})")
                return

        if not conn:
            output_error(f"Failed to connect to device at {ip}:{port}")
            return

        # Disable device during sync to prevent inconsistencies
        try:
            conn.disable_device()
        except:
            pass

        # Get device info
        device_info = {}
        try:
            device_info = {
                "serial_number": conn.get_serialnumber() or "Unknown",
                "firmware_version": conn.get_firmware_version() or "Unknown",
                "device_name": conn.get_device_name() or "ZKTeco Device",
            }
        except Exception:
            device_info = {"serial_number": "Unknown", "firmware_version": "Unknown"}

        # Get users
        users = []
        try:
            device_users = conn.get_users()
            for user in device_users:
                users.append({
                    "user_id": str(user.user_id),
                    "name": user.name or f"User {user.user_id}",
                    "privilege": user.privilege if hasattr(user, 'privilege') else 0,
                    "card": user.card if hasattr(user, 'card') else None,
                })
        except Exception:
            pass

        # Get attendance logs - filter by date range
        attendance = []
        cutoff_date = datetime.now() - timedelta(days=days_back)

        try:
            logs = conn.get_attendance()
            for log in logs:
                # Filter: only include records from the last N days
                if log.timestamp >= cutoff_date:
                    attendance.append({
                        "user_id": str(log.user_id),
                        "timestamp": log.timestamp.strftime("%Y-%m-%d %H:%M:%S"),
                        "status": log.status if hasattr(log, 'status') else 0,
                        "punch": log.punch if hasattr(log, 'punch') else 0,
                    })
        except Exception:
            pass

        # Re-enable device
        try:
            conn.enable_device()
        except:
            pass

        # Disconnect
        try:
            conn.disconnect()
        except:
            pass

        # Cancel the alarm
        signal.alarm(0)

        # Output success
        output_json({
            "success": True,
            "users": users,
            "attendance": attendance,
            "device_info": device_info,
            "sync_time": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
            "total_users": len(users),
            "total_attendance": len(attendance),
            "days_synced": days_back
        })

    except BrokenPipeError as e:
        signal.alarm(0)
        if conn:
            try:
                conn.enable_device()
                conn.disconnect()
            except:
                pass
        output_error("Connection lost (Broken pipe). Device may have closed connection. Try: 1) Restart device 2) Check network stability 3) Reduce sync frequency")
    except Exception as e:
        signal.alarm(0)
        if conn:
            try:
                conn.enable_device()
                conn.disconnect()
            except:
                pass
        error_msg = str(e)
        # Make error message more helpful
        if "Broken pipe" in error_msg or "errno 32" in error_msg.lower():
            error_msg = "Connection lost during sync. Device closed connection unexpectedly. Try restarting the device."
        elif "timeout" in error_msg.lower():
            error_msg = "Device timeout. Device may be busy or have too many records. Try syncing fewer days."
        output_error(error_msg)

def main():
    if len(sys.argv) < 3:
        output_error("Usage: python3 zkteco_sync.py <device_ip> <device_port> [days_back]")
        return

    device_ip = sys.argv[1]
    device_port = sys.argv[2]
    days_back = int(sys.argv[3]) if len(sys.argv) > 3 else 7

    # Validate IP format (basic check)
    parts = device_ip.split('.')
    if len(parts) != 4:
        output_error(f"Invalid IP address format: {device_ip}")
        return

    # Validate port
    try:
        port = int(device_port)
        if port < 1 or port > 65535:
            raise ValueError("Port out of range")
    except ValueError:
        output_error(f"Invalid port number: {device_port}")
        return

    sync_device(device_ip, port, days_back)

if __name__ == "__main__":
    main()
