<?php

namespace App\Support;

use Closure;

/**
 * SSRF guard for server-side requests to administrator-configured URLs.
 *
 * A URL passes only when it is https on port 443, carries no credentials, names
 * a host on the allow-list (exact or subdomain match, never an IP literal), and
 * every address the host resolves to is public. check() returns the address to
 * pin the connection to, so a DNS answer can't change between check and request
 * (DNS rebinding).
 */
class OutboundUrlGuard
{
    /** @param  (Closure(string): array<int, string>)|null  $resolver  host => IP list (injectable for tests) */
    public function __construct(private ?Closure $resolver = null) {}

    /**
     * @param  array<int, string>  $allowedHosts
     * @return array{host: string, ip: string, port: int}
     *
     * @throws UnsafeUrlException
     */
    public function check(string $url, array $allowedHosts): array
    {
        $parts = parse_url(trim($url));

        if ($parts === false || empty($parts['host']) || empty($parts['scheme'])) {
            throw new UnsafeUrlException('The URL is not valid.');
        }

        if (strtolower($parts['scheme']) !== 'https') {
            throw new UnsafeUrlException('Only https:// URLs are allowed.');
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new UnsafeUrlException('URLs must not contain credentials.');
        }

        $port = $parts['port'] ?? 443;
        if ($port !== 443) {
            throw new UnsafeUrlException('Only the standard https port (443) is allowed.');
        }

        $host = strtolower(rtrim($parts['host'], '.'));

        if (filter_var(trim($host, '[]'), FILTER_VALIDATE_IP)) {
            throw new UnsafeUrlException('Use the provider\'s host name, not an IP address.');
        }

        if (! $this->hostAllowed($host, $allowedHosts)) {
            throw new UnsafeUrlException('The host "'.$host.'" is not an approved SMS provider host. Allowed: '.(implode(', ', $allowedHosts) ?: 'none configured').'.');
        }

        $ips = $this->resolve($host);

        if ($ips === []) {
            throw new UnsafeUrlException('The host "'.$host.'" could not be resolved.');
        }

        foreach ($ips as $ip) {
            if (! self::isPublicIp($ip)) {
                throw new UnsafeUrlException('The host "'.$host.'" resolves to a private or reserved address.');
            }
        }

        return ['host' => $host, 'ip' => $ips[0], 'port' => $port];
    }

    /** cURL option that pins the request to the checked address while keeping SNI/certificate checks on the host name. */
    public static function pinOption(array $checked): array
    {
        $ip = str_contains($checked['ip'], ':') ? '['.$checked['ip'].']' : $checked['ip'];

        return [CURLOPT_RESOLVE => [$checked['host'].':'.$checked['port'].':'.$ip]];
    }

    public static function isPublicIp(string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        // Ranges PHP's flags don't cover: carrier-grade NAT, IPv4-mapped/NAT64 IPv6, unique-local IPv6
        foreach (['100.64.0.0/10', '192.0.0.0/24', '198.18.0.0/15'] as $cidr) {
            if (self::inIpv4Cidr($ip, $cidr)) {
                return false;
            }
        }

        $lower = strtolower($ip);

        return ! (str_starts_with($lower, '::ffff:') || str_starts_with($lower, '64:ff9b:') || preg_match('/^f[cd][0-9a-f]{2}:/', $lower));
    }

    private function hostAllowed(string $host, array $allowedHosts): bool
    {
        foreach ($allowedHosts as $allowed) {
            $allowed = strtolower(trim($allowed, ". \t"));

            if ($allowed !== '' && ($host === $allowed || str_ends_with($host, '.'.$allowed))) {
                return true;
            }
        }

        return false;
    }

    /** @return array<int, string> */
    private function resolve(string $host): array
    {
        if ($this->resolver) {
            return array_values(($this->resolver)($host));
        }

        $ips = gethostbynamel($host) ?: [];

        foreach (@dns_get_record($host, DNS_AAAA) ?: [] as $record) {
            if (! empty($record['ipv6'])) {
                $ips[] = $record['ipv6'];
            }
        }

        return array_values(array_unique($ips));
    }

    private static function inIpv4Cidr(string $ip, string $cidr): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        [$subnet, $bits] = explode('/', $cidr);
        $mask = -1 << (32 - (int) $bits);

        return (ip2long($ip) & $mask) === (ip2long($subnet) & $mask);
    }
}
