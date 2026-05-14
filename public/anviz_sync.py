#!/usr/bin/env python3
"""
Anviz Fingerprint Device Sync Script
This script connects to an Anviz device via HTTP API, retrieves users and attendance logs,
and outputs JSON data for Laravel to process.

Usage: python3 anviz_sync.py <device_ip> [days_back]
"""

import sys
import json
import requests
from datetime import datetime, timedelta
from requests.auth import HTTPBasicAuth

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

def sync_anviz_device(ip, days_back=7, username='admin', password='admin'):
    """
    Connect to Anviz device via HTTP API and retrieve data

    Anviz devices typically use HTTP/HTTPS API on port 80/443
    Various protocols are attempted based on different Anviz models:
    1. HTTP REST API (newer models)
    2. CGI-based API (older models)
    3. ADMS protocol (legacy)
    """

    users = []
    attendance = []
    device_info = {"ip_address": ip, "attempts": []}

    # Try different base URLs (HTTP and HTTPS)
    base_urls = [
        f"http://{ip}",
        f"http://{ip}:80",
        f"http://{ip}:8080",
        f"https://{ip}",
    ]

    try:
        # Try to get device info
        try:
            response = session.get(f"{base_url}/cgi-bin/deviceinfo.cgi", timeout=10)
            if response.status_code == 200:
                device_info = {
                    "ip_address": ip,
                    "status": "online",
                    "model": "Anviz Device"
                }
        except Exception as e:
            # Device info not critical, continue
            device_info = {"ip_address": ip, "status": "unknown"}

        # Get users from device
        # Anviz API varies by model, try common endpoints
        user_endpoints = [
            "/cgi-bin/recordFinger.cgi?action=list",
            "/cgi-bin/userinfo.cgi",
            "/api/users",
        ]

        for endpoint in user_endpoints:
            try:
                response = session.get(f"{base_url}{endpoint}", timeout=15)
                if response.status_code == 200:
                    # Try to parse response
                    data = response.text
                    # Anviz typically returns custom format, try to parse
                    # Format varies by model - this is a generic parser
                    lines = data.strip().split('\n')
                    for line in lines:
                        if 'USER' in line.upper() or 'ID' in line:
                            parts = line.split(',')
                            if len(parts) >= 2:
                                user_id = parts[0].strip()
                                user_name = parts[1].strip() if len(parts) > 1 else f"User {user_id}"
                                users.append({
                                    "user_id": user_id,
                                    "name": user_name,
                                })
                    if users:
                        break
            except Exception:
                continue

        # Get attendance logs
        # Calculate date range
        end_date = datetime.now()
        start_date = end_date - timedelta(days=days_back)

        attendance_endpoints = [
            f"/cgi-bin/attendance.cgi?action=list&startdate={start_date.strftime('%Y-%m-%d')}&enddate={end_date.strftime('%Y-%m-%d')}",
            f"/cgi-bin/recordFinger.cgi?action=attendance&startdate={start_date.strftime('%Y%m%d')}&enddate={end_date.strftime('%Y%m%d')}",
            "/api/attendance",
        ]

        for endpoint in attendance_endpoints:
            try:
                response = session.get(f"{base_url}{endpoint}", timeout=30)
                if response.status_code == 200:
                    data = response.text
                    # Parse attendance data
                    # Common format: USER_ID, TIMESTAMP, STATUS
                    lines = data.strip().split('\n')
                    for line in lines:
                        parts = line.split(',')
                        if len(parts) >= 2:
                            try:
                                user_id = parts[0].strip()
                                timestamp_str = parts[1].strip()

                                # Try to parse timestamp (various formats)
                                timestamp = None
                                for fmt in ["%Y-%m-%d %H:%M:%S", "%Y/%m/%d %H:%M:%S", "%Y%m%d%H%M%S"]:
                                    try:
                                        timestamp = datetime.strptime(timestamp_str, fmt)
                                        break
                                    except:
                                        continue

                                if timestamp and timestamp >= start_date:
                                    attendance.append({
                                        "user_id": user_id,
                                        "timestamp": timestamp.strftime("%Y-%m-%d %H:%M:%S"),
                                        "status": 0,
                                        "punch": 0,
                                    })
                            except Exception:
                                continue
                    if attendance:
                        break
            except Exception:
                continue

        # If we got some data, consider it a success
        if users or attendance:
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
        else:
            output_error("Could not retrieve data from Anviz device. Device may use different API. Please check: 1) Device model/firmware 2) API credentials 3) Network access")

    except requests.exceptions.Timeout:
        output_error("Connection timeout. Check if device is reachable and responding.")
    except requests.exceptions.ConnectionError:
        output_error("Cannot connect to device. Check IP address and network connectivity.")
    except Exception as e:
        output_error(f"Error syncing Anviz device: {str(e)}")

def main():
    if len(sys.argv) < 2:
        output_error("Usage: python3 anviz_sync.py <device_ip> [days_back] [username] [password]")
        return

    device_ip = sys.argv[1]
    days_back = int(sys.argv[2]) if len(sys.argv) > 2 else 7
    username = sys.argv[3] if len(sys.argv) > 3 else 'admin'
    password = sys.argv[4] if len(sys.argv) > 4 else 'admin'

    # Validate IP format (basic check)
    parts = device_ip.split('.')
    if len(parts) != 4:
        output_error(f"Invalid IP address format: {device_ip}")
        return

    sync_anviz_device(device_ip, days_back, username, password)

if __name__ == "__main__":
    main()
