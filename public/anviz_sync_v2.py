#!/usr/bin/env python3
"""
Enhanced Anviz Fingerprint Device Sync Script
Supports multiple Anviz protocols and models

Usage: python3 anviz_sync_v2.py <device_ip> [days_back] [username] [password]
"""

import sys
import json
import socket
import struct
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

def try_tcp_protocol(ip, port=5010, timeout=3):
    """
    Try Anviz TCP protocol (default port 5010)

    Anviz devices commonly use TCP socket communication on port 5010
    Protocol: Binary packets with specific structure
    """
    users = []
    attendance = []

    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(timeout)
        sock.connect((ip, port))

        # Anviz protocol: Send handshake/request
        # Header structure varies by model, trying common patterns

        # Request attendance records (command code varies by model)
        # Common command codes: 0x40 (attendance), 0x72 (user list)

        # For now, just verify connection and return info
        sock.close()

        return {
            "protocol": "TCP",
            "port": port,
            "connected": True
        }
    except socket.timeout:
        return {"protocol": "TCP", "error": "Connection timeout"}
    except ConnectionRefusedError:
        return {"protocol": "TCP", "error": "Connection refused"}
    except Exception as e:
        return {"protocol": "TCP", "error": str(e)}

def try_http_api(ip, username='admin', password='admin', timeout=3):
    """
    Try HTTP/HTTPS API endpoints
    Different Anviz models use different API structures
    """
    results = {"attempts": [], "users": [], "attendance": []}

    # Try different base URLs and ports
    protocols = [
        ("http", 80),
        ("http", 8080),
        ("https", 443),
    ]

    for proto, port in protocols:
        base_url = f"{proto}://{ip}:{port}"

        try:
            session = requests.Session()
            session.auth = HTTPBasicAuth(username, password)

            # Try various endpoint patterns
            endpoints_to_try = [
                # Device info endpoints
                ("/cgi-bin/deviceinfo.cgi", "GET", None),
                ("/device/info", "GET", None),
                ("/api/device", "GET", None),

                # User list endpoints
                ("/cgi-bin/recordFinger.cgi?action=list", "GET", None),
                ("/cgi-bin/userinfo.cgi", "GET", None),
                ("/api/users", "GET", None),
                ("/user/list", "GET", None),

                # Attendance endpoints
                ("/cgi-bin/attendance.cgi?action=list", "GET", None),
                ("/api/attendance", "GET", None),
                ("/attendance/records", "GET", None),
            ]

            for endpoint, method, data in endpoints_to_try:
                try:
                    url = f"{base_url}{endpoint}"

                    if method == "GET":
                        response = session.get(url, timeout=timeout, verify=False)
                    else:
                        response = session.post(url, json=data, timeout=timeout, verify=False)

                    if response.status_code == 200:
                        results["attempts"].append({
                            "url": url,
                            "status": "success",
                            "content_length": len(response.text),
                            "preview": response.text[:200]
                        })

                        # Try to parse the response
                        content = response.text

                        # Check if it's JSON
                        try:
                            json_data = response.json()
                            if isinstance(json_data, dict):
                                if 'users' in json_data:
                                    results["users"] = json_data['users']
                                if 'attendance' in json_data or 'records' in json_data:
                                    results["attendance"] = json_data.get('attendance', json_data.get('records', []))
                        except:
                            # Not JSON, try CSV/text parsing
                            if content and len(content) > 10:
                                results["attempts"].append({
                                    "url": url,
                                    "note": "Non-JSON response, may need custom parser"
                                })
                    else:
                        results["attempts"].append({
                            "url": url,
                            "status": response.status_code
                        })

                except requests.exceptions.Timeout:
                    continue
                except requests.exceptions.ConnectionError:
                    continue
                except Exception as e:
                    results["attempts"].append({
                        "url": url if 'url' in locals() else endpoint,
                        "error": str(e)[:100]
                    })

        except Exception as e:
            continue

    return results

def sync_anviz_device(ip, days_back=7, username='admin', password='admin'):
    """
    Main sync function - tries multiple protocols
    """
    debug_info = {
        "ip": ip,
        "protocols_tried": []
    }

    # 1. Try TCP protocol (most Anviz devices use this)
    tcp_result = try_tcp_protocol(ip)
    debug_info["protocols_tried"].append(tcp_result)

    # 2. Try HTTP/HTTPS API
    http_result = try_http_api(ip, username, password)
    debug_info["protocols_tried"].append(http_result)

    # Check if we got any data
    users = http_result.get("users", [])
    attendance = http_result.get("attendance", [])

    if users or attendance:
        output_json({
            "success": True,
            "users": users,
            "attendance": attendance,
            "device_info": debug_info,
            "sync_time": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
            "total_users": len(users),
            "total_attendance": len(attendance),
            "days_synced": days_back
        })
    else:
        # Provide detailed debug information
        error_msg = "Could not retrieve data from Anviz device.\n\n"
        error_msg += "DEBUG INFORMATION:\n"
        error_msg += f"IP Address: {ip}\n"

        if tcp_result.get("connected"):
            error_msg += f"✓ TCP connection successful on port {tcp_result.get('port')}\n"
            error_msg += "  Note: Device uses TCP protocol. Python library 'pyanviz' may be needed.\n"
        else:
            error_msg += f"✗ TCP connection failed: {tcp_result.get('error', 'Unknown')}\n"

        if http_result.get("attempts"):
            error_msg += f"\nHTTP API Attempts: {len(http_result['attempts'])}\n"
            for attempt in http_result['attempts'][:5]:  # Show first 5
                if 'url' in attempt:
                    error_msg += f"  - {attempt['url']}: {attempt.get('status', attempt.get('error', 'N/A'))}\n"

        error_msg += "\nRECOMMENDATIONS:\n"
        error_msg += "1. Check device model and firmware version\n"
        error_msg += "2. Verify admin credentials (current: " + username + ")\n"
        error_msg += "3. Check if device has HTTP API enabled\n"
        error_msg += "4. Consider using Anviz CrossChex software for initial setup\n"
        error_msg += "5. Device may require specific Python library (pyanviz)\n"

        output_error(error_msg)

def main():
    if len(sys.argv) < 2:
        output_error("Usage: python3 anviz_sync_v2.py <device_ip> [days_back] [username] [password]")
        return

    device_ip = sys.argv[1]
    days_back = int(sys.argv[2]) if len(sys.argv) > 2 else 7
    username = sys.argv[3] if len(sys.argv) > 3 else 'admin'
    password = sys.argv[4] if len(sys.argv) > 4 else 'admin'

    # Validate IP format
    parts = device_ip.split('.')
    if len(parts) != 4:
        output_error(f"Invalid IP address format: {device_ip}")
        return

    # Disable SSL warnings for self-signed certs
    import urllib3
    urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

    sync_anviz_device(device_ip, days_back, username, password)

if __name__ == "__main__":
    main()
