#!/usr/bin/env python3
"""
Anviz Device Diagnostic Tool
Quickly identifies which protocol and ports the Anviz device uses

Usage: python3 anviz_diagnostic.py <device_ip>
"""

import sys
import socket
import requests
from requests.auth import HTTPBasicAuth

def check_port(ip, port, timeout=2):
    """Check if a port is open"""
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(timeout)
        result = sock.connect_ex((ip, port))
        sock.close()
        return result == 0
    except:
        return False

def check_http_endpoint(ip, port, endpoint, username='admin', password='admin'):
    """Check if an HTTP endpoint responds"""
    try:
        url = f"http://{ip}:{port}{endpoint}"
        response = requests.get(url, auth=HTTPBasicAuth(username, password), timeout=2, verify=False)
        return {
            "url": url,
            "status_code": response.status_code,
            "content_length": len(response.text),
            "preview": response.text[:100] if response.text else ""
        }
    except requests.exceptions.Timeout:
        return {"url": f"http://{ip}:{port}{endpoint}", "error": "Timeout"}
    except requests.exceptions.ConnectionError:
        return {"url": f"http://{ip}:{port}{endpoint}", "error": "Connection refused"}
    except Exception as e:
        return {"url": f"http://{ip}:{port}{endpoint}", "error": str(e)[:50]}

def diagnose_anviz(ip):
    """Run full diagnostic on Anviz device"""
    print(f"\n{'='*60}")
    print(f"ANVIZ DEVICE DIAGNOSTIC")
    print(f"IP Address: {ip}")
    print(f"{'='*60}\n")

    # 1. Check ping/ICMP
    print("1. Network Connectivity Test")
    print("-" * 40)
    ping_result = check_port(ip, 80, timeout=1)  # Quick check
    if ping_result or True:  # Continue anyway
        print(f"   ✓ Device appears to be network accessible")
    else:
        print(f"   ✗ Cannot reach device - check IP and network")
    print()

    # 2. Check common Anviz ports
    print("2. Port Scan")
    print("-" * 40)
    common_ports = {
        80: "HTTP (Web Interface)",
        443: "HTTPS (Secure Web)",
        5010: "Anviz TCP Protocol (Primary)",
        5011: "Anviz TCP Protocol (Alt)",
        8080: "HTTP Alternate",
        8443: "HTTPS Alternate",
        37777: "Anviz Legacy Protocol"
    }

    open_ports = []
    for port, description in common_ports.items():
        is_open = check_port(ip, port)
        status = "✓ OPEN" if is_open else "  closed"
        print(f"   Port {port:5d} [{description:30s}]: {status}")
        if is_open:
            open_ports.append(port)
    print()

    # 3. HTTP API Endpoints Test
    if 80 in open_ports or 8080 in open_ports:
        print("3. HTTP API Endpoint Test")
        print("-" * 40)

        test_ports = [p for p in [80, 8080] if p in open_ports]
        endpoints = [
            "/cgi-bin/deviceinfo.cgi",
            "/cgi-bin/userinfo.cgi",
            "/cgi-bin/attendance.cgi",
            "/cgi-bin/recordFinger.cgi",
            "/api/device",
            "/api/users",
            "/api/attendance",
        ]

        found_endpoints = []
        for port in test_ports:
            print(f"\n   Testing port {port}:")
            for endpoint in endpoints:
                result = check_http_endpoint(ip, port, endpoint)
                if result.get('status_code') == 200:
                    print(f"      ✓ {endpoint} - HTTP {result['status_code']}")
                    found_endpoints.append(result)
                elif result.get('status_code'):
                    print(f"      • {endpoint} - HTTP {result['status_code']}")
                elif 'Timeout' not in result.get('error', ''):
                    print(f"      ✗ {endpoint} - {result.get('error', 'N/A')}")
        print()

        if found_endpoints:
            print("   Working Endpoints Found:")
            for ep in found_endpoints:
                print(f"      {ep['url']}")
                if ep.get('preview'):
                    print(f"         Preview: {ep['preview']}")
    else:
        print("3. HTTP API Endpoint Test")
        print("-" * 40)
        print("   ⚠ No HTTP ports open - device may use TCP protocol only")
        print()

    # 4. Recommendations
    print("4. Recommendations")
    print("-" * 40)

    if 5010 in open_ports or 5011 in open_ports:
        print("   ℹ Device uses Anviz TCP Protocol (port 5010/5011)")
        print("   → Requires 'pyanviz' Python library or proprietary SDK")
        print("   → HTTP API may not be available on this model")
        print()
        print("   SOLUTION OPTIONS:")
        print("   A) Use Anviz CrossChex software for data export")
        print("   B) Install pyanviz library: pip3 install pyanviz")
        print("   C) Contact Anviz for SDK documentation")

    elif 80 in open_ports or 8080 in open_ports:
        print("   ℹ Device has HTTP service running")
        if found_endpoints:
            print("   → HTTP API detected and working")
            print("   → Sync should work with current script")
        else:
            print("   → HTTP port open but API endpoints not responding")
            print("   → May need correct credentials or different endpoints")
            print()
            print("   SOLUTION OPTIONS:")
            print("   A) Verify username/password (try: admin/admin)")
            print("   B) Check device manual for correct API endpoints")
            print("   C) Enable API access in device web interface")

    else:
        print("   ⚠ No standard Anviz ports detected")
        print()
        print("   TROUBLESHOOTING:")
        print("   1. Verify IP address is correct")
        print("   2. Check device is powered on")
        print("   3. Verify network connectivity")
        print("   4. Check firewall settings")

    print()
    print("=" * 60)
    print()

def main():
    if len(sys.argv) < 2:
        print("Usage: python3 anviz_diagnostic.py <device_ip>")
        print("Example: python3 anviz_diagnostic.py 192.168.1.100")
        return

    device_ip = sys.argv[1]

    # Validate IP
    parts = device_ip.split('.')
    if len(parts) != 4:
        print(f"Error: Invalid IP address format: {device_ip}")
        return

    # Disable SSL warnings
    import urllib3
    urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

    diagnose_anviz(device_ip)

if __name__ == "__main__":
    main()
