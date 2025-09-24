<?php
// Csrf.php
class Csrf
{
    private string $secret;     // set dari env/config
    private string $sessionKey; // nama indeks di $_SESSION untuk single-use bookkeeping
    private int $defaultTtl;    // detik

    public function __construct(string $secret, int $defaultTtl = 1800, string $sessionKey = '__csrf_used')
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session must be started before using Csrf.');
        }
        if ($secret === '' || strlen($secret) < 32) {
            throw new InvalidArgumentException('CSRF secret must be a non-empty, sufficiently long string (>=32 chars).');
        }
        $this->secret = $secret;
        $this->defaultTtl = $defaultTtl;
        $this->sessionKey = $sessionKey;
        $_SESSION[$this->sessionKey] ??= [];
    }

    /**
     * Generate a token for a specific action/key.
     * @param string $key Identifier for the form/action (e.g., "login_form").
     * @param int|null $ttl Token TTL in seconds (null => default).
     * @param bool $singleUse If true, mark nonce in session after validation to prevent reuse.
     */
    public function generate(string $key, ?int $ttl = null, bool $singleUse = true): string
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $exp = time() + max(60, $ttl);          // minimal 60 detik
        $nonce = bin2hex(random_bytes(16));     // 32 hex chars
        $sid = session_id();

        // data yang dilindungi HMAC
        $data = $key.'|'.$exp.'|'.$nonce.'|'.$sid;

        // kunci derivasi sederhana: secret + sid, supaya token terikat ke sesi
        $mac = hash_hmac('sha256', $data, $this->secret.$sid, true);

        // kemas: key|exp|nonce|mac (sid tidak diekspose)
        $packed = $key.'|'.$exp.'|'.$nonce.'|'.self::b64url_encode($mac);

        // Masking sederhana untuk mengurangi risiko BREACH (opsional ringan)
        return self::b64url_encode($packed);
    }

    /**
     * Validate token for a given key.
     * @return bool
     */
    public function validate(string $key, ?string $token, bool $singleUse = true): bool
    {
        if (!$token) return false;

        $decoded = self::b64url_decode($token);
        if ($decoded === false) return false;

        $parts = explode('|', $decoded);
        if (count($parts) !== 4) return false;

        [$tKey, $tExp, $tNonce, $tMacB64] = $parts;
        if (!ctype_digit((string)$tExp)) return false;
        if ($tKey !== $key) return false;            // key harus persis sama
        if ((int)$tExp < time()) return false;       // expired

        $macBin = self::b64url_decode($tMacB64);
        if ($macBin === false) return false;

        $sid = session_id();
        $data = $tKey.'|'.$tExp.'|'.$tNonce.'|'.$sid;
        $calcMac = hash_hmac('sha256', $data, $this->secret.$sid, true);

        if (!hash_equals($calcMac, $macBin)) {
            return false;
        }

        // optional single-use: tolak jika nonce sudah pernah dipakai
        if ($singleUse) {
            $used = $_SESSION[$this->sessionKey];
            if (!empty($used[$tKey][$tNonce])) {
                return false; // replay
            }
            // tandai dipakai & bersihkan yang kadaluarsa
            $_SESSION[$this->sessionKey][$tKey][$tNonce] = (int)$tExp;
            $this->gcUsed();
        }

        return true;
    }

    /**
     * Helper untuk render hidden input ke HTML form.
     */
    public function inputField(string $key, string $name = '_token', ?int $ttl = null, bool $singleUse = true): string
    {
        $tok = $this->generate($key, $ttl, $singleUse);
        $safe = htmlspecialchars($tok, ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="'.$name.'" value="'.$safe.'">';
    }

    /**
     * Ambil token untuk AJAX dan kirim di header: X-CSRF-Token
     */
    public function getToken(string $key, ?int $ttl = null, bool $singleUse = true): string
    {
        return $this->generate($key, $ttl, $singleUse);
    }

    private function gcUsed(): void
    {
        $now = time();
        foreach ($_SESSION[$this->sessionKey] as $k => $nonces) {
            foreach ($nonces as $nonce => $exp) {
                if ($exp < $now) {
                    unset($_SESSION[$this->sessionKey][$k][$nonce]);
                }
            }
            if (empty($_SESSION[$this->sessionKey][$k])) {
                unset($_SESSION[$this->sessionKey][$k]);
            }
        }
    }

    private static function b64url_encode(string $bin): string
    {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }

    private static function b64url_decode(string $str)
    {
        $pad = strlen($str) % 4;
        if ($pad) $str .= str_repeat('=', 4 - $pad);
        $bin = base64_decode(strtr($str, '-_', '+/'), true);
        return $bin === false ? false : $bin;
    }
}
