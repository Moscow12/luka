#!/usr/bin/env python3
"""
Anviz TCP Protocol Sync Script
For Anviz devices that use proprietary TCP protocol on port 5010/5011

Usage: python3 anviz_tcp_sync.py <device_ip> [days_back] [port]
"""

import sys
import json
import socket
import struct
import time
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

def calculate_checksum(data):
    """Calculate Anviz protocol checksum"""
    checksum = 0
    for byte in data:
        checksum ^= byte
    return checksum

def create_anviz_packet(command, data=b''):
    """
    Create Anviz TCP protocol packet

    Packet structure:
    - STX (1 byte): 0xA5
    - Length (2 bytes): Total packet length
    - Command (1 byte): Command code
    - Data (variable)
    - CHK (1 byte): XOR checksum
    - ETX (1 byte): 0x5A
    """
    STX = 0xA5
    ETX = 0x5A

    # Build packet without checksum
    packet = bytearray()
    packet.append(STX)

    # Length (includes command + data + checksum)
    length = 1 + len(data) + 1  # command + data + checksum
    packet.extend(struct.pack('<H', length))

    # Command
    packet.append(command)

    # Data
    packet.extend(data)

    # Calculate checksum (XOR of all bytes except STX, Length, and ETX)
    checksum = calculate_checksum(packet[3:])
    packet.append(checksum)

    # ETX
    packet.append(ETX)

    return bytes(packet)

def parse_anviz_response(response):
    """Parse Anviz TCP protocol response"""
    if len(response) < 6:
        return None

    # Check STX and ETX
    if response[0] != 0xA5 or response[-1] != 0x5A:
        return None

    # Extract fields
    length = struct.unpack('<H', response[1:3])[0]
    command = response[3]
    data = response[4:-2]  # Exclude checksum and ETX
    checksum = response[-2]

    return {
        'command': command,
        'data': data,
        'length': length
    }

def connect_anviz_tcp(ip, port=5010, timeout=10):
    """Connect to Anviz device via TCP"""
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(timeout)
        sock.connect((ip, port))
        return sock
    except Exception as e:
        raise Exception(f"Connection failed: {str(e)}")

def get_device_info(sock):
    """Get device information (Command: 0x30)"""
    # Command 0x30: Get device info
    packet = create_anviz_packet(0x30)
    sock.send(packet)

    response = sock.recv(1024)
    parsed = parse_anviz_response(response)

    if parsed:
        return {
            "model": "Anviz Device",
            "firmware": "Unknown"
        }
    return {}

def get_user_list(sock):
    """
    Get user list from device
    Command varies by model:
    - 0x72: Get all users (common)
    - 0x73: Get user by ID
    """
    users = []

    # Try command 0x72 (get all users)
    try:
        packet = create_anviz_packet(0x72)
        sock.send(packet)
        time.sleep(0.2)

        response = sock.recv(8192)
        parsed = parse_anviz_response(response)

        if parsed and len(parsed['data']) > 0:
            # Parse user data
            # Format varies by model - this is a simplified parser
            data = parsed['data']
            offset = 0

            while offset < len(data) - 10:
                try:
                    # User ID (typically 4 bytes)
                    user_id = struct.unpack('<I', data[offset:offset+4])[0]
                    if user_id == 0 or user_id > 999999:
                        break

                    offset += 4

                    # Name length and name (varies by model)
                    # Skipping complex parsing for now
                    users.append({
                        "user_id": str(user_id),
                        "name": f"User {user_id}"
                    })

                    offset += 20  # Skip to next user record
                except:
                    break
    except Exception as e:
        pass

    return users

def get_attendance_records(sock, days_back=7):
    """
    Get attendance records
    Command 0x40: Get attendance logs
    """
    attendance = []

    try:
        # Calculate date range
        end_date = datetime.now()
        start_date = end_date - timedelta(days=days_back)

        # Build data packet with date range
        # Format: Start date (4 bytes) + End date (4 bytes)
        start_timestamp = int(start_date.timestamp())
        end_timestamp = int(end_date.timestamp())

        data = struct.pack('<II', start_timestamp, end_timestamp)

        # Command 0x40: Get attendance
        packet = create_anviz_packet(0x40, data)
        sock.send(packet)
        time.sleep(0.5)

        # May need multiple receives for large datasets
        response = sock.recv(65536)
        parsed = parse_anviz_response(response)

        if parsed and len(parsed['data']) > 0:
            # Parse attendance data
            data = parsed['data']
            offset = 0

            # Record format (varies by model):
            # User ID (4 bytes) + Timestamp (4 bytes) + Type (1 byte) + ...
            record_size = 16  # Approximate

            while offset < len(data) - record_size:
                try:
                    user_id = struct.unpack('<I', data[offset:offset+4])[0]
                    if user_id == 0 or user_id > 999999:
                        break

                    timestamp = struct.unpack('<I', data[offset+4:offset+8])[0]

                    # Convert timestamp to datetime
                    dt = datetime.fromtimestamp(timestamp)

                    attendance.append({
                        "user_id": str(user_id),
                        "timestamp": dt.strftime("%Y-%m-%d %H:%M:%S"),
                        "status": 0,
                        "punch": 0,
                    })

                    offset += record_size
                except Exception as e:
                    break
    except Exception as e:
        pass

    return attendance

def sync_anviz_tcp(ip, days_back=7, port=5010):
    """Main sync function for TCP protocol"""

    sock = None
    try:
        # Connect to device
        sock = connect_anviz_tcp(ip, port)

        # Get device info
        device_info = get_device_info(sock)

        # Get users
        users = get_user_list(sock)

        # Get attendance
        attendance = get_attendance_records(sock, days_back)

        # Close connection
        sock.close()

        # If we got users but no attendance, that's still partial success
        if users or attendance:
            output_json({
                "success": True,
                "users": users,
                "attendance": attendance,
                "device_info": device_info,
                "sync_time": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                "total_users": len(users),
                "total_attendance": len(attendance),
                "days_synced": days_back,
                "protocol": "TCP",
                "note": "Basic TCP protocol parsing. Results may vary by device model."
            })
        else:
            output_error(
                "TCP connection successful but could not parse data.\n\n"
                "This script uses basic Anviz TCP protocol parsing which may not work with all models.\n\n"
                "RECOMMENDED SOLUTIONS:\n"
                "1. Use Anviz CrossChex Standard software (free download from Anviz)\n"
                "2. Export attendance data from CrossChex to CSV\n"
                "3. Import CSV into this system\n\n"
                "OR contact support to implement model-specific protocol."
            )

    except Exception as e:
        if sock:
            sock.close()
        output_error(f"TCP sync failed: {str(e)}")

def main():
    if len(sys.argv) < 2:
        output_error("Usage: python3 anviz_tcp_sync.py <device_ip> [days_back] [port]")
        return

    device_ip = sys.argv[1]
    days_back = int(sys.argv[2]) if len(sys.argv) > 2 else 7
    port = int(sys.argv[3]) if len(sys.argv) > 3 else 5010

    # Validate IP
    parts = device_ip.split('.')
    if len(parts) != 4:
        output_error(f"Invalid IP address: {device_ip}")
        return

    sync_anviz_tcp(device_ip, days_back, port)

if __name__ == "__main__":
    main()
