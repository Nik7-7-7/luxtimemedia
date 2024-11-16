<?php
namespace RSSSL\Pro\Security\WordPress\Firewall\Models;

class Rsssl_User_Agent_Handler
{
    private $user_agent;
    private bool $is_mobile = false;
    private bool $is_bot = false;
    private bool $is_desktop = false;
    private bool $is_curl = false;
    private $named_user_agents;

    public function __construct()
    {
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $this->detect_device_type();
        $this->detect_if_bot();
        $this->detect_if_curl();
    }

    private function detect_device_type(): void
    {
        $mobileAgents = ['iPhone', 'Android', 'webOS', 'BlackBerry', 'iPod', 'Windows Phone'];
        $desktopAgents = ['Windows NT', 'Macintosh', 'X11', 'Linux'];

        foreach ($mobileAgents as $agent) {
            if (stripos($this->user_agent, $agent) !== false) {
                $this->is_mobile = true;
                return;
            }
        }

        foreach ($desktopAgents as $agent) {
            if (stripos($this->user_agent, $agent) !== false) {
                $this->is_desktop = true;
                return;
            }
        }
    }

    private function detect_if_bot(): void
    {
        $botAgents = ['Googlebot', 'Bingbot', 'Slurp', 'DuckDuckBot', 'Baiduspider', 'YandexBot', 'Sogou'];

        foreach ($botAgents as $bot) {
            if (stripos($this->user_agent, $bot) !== false) {
                $this->is_bot = true;
                return;
            }
        }
    }

    private function detect_if_curl(): void
    {
        // Check if the User-Agent string contains 'curl'
        if (stripos($this->user_agent, 'curl') !== false) {
            $this->is_curl = true;
            return;
        }

        // Check for common characteristics of a curl request
        $curlIndicators = [
            'HTTP_ACCEPT' => '*/*', // Default Accept header for curl
            'HTTP_CONNECTION' => 'Keep-Alive', // Often used by curl
            'HTTP_ACCEPT_ENCODING' => '', // curl may not send this header
        ];

        foreach ($curlIndicators as $header => $value) {
            if (isset($_SERVER[$header]) && $_SERVER[$header] === $value) {
                $this->is_curl = true;
                return;
            }
        }

        // If the User-Agent does not contain 'curl', but other curl-specific patterns are found
        if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && empty($_SERVER['HTTP_REFERER'])) {
            $this->is_curl = true;
        }
    }

    public function get_user_agent(): string
    {
        return $this->user_agent;
    }

    public function is_mobile(): bool
    {
        return $this->is_mobile;
    }

    public function is_desktop(): bool
    {
        return $this->is_desktop;
    }

    public function is_bot(): bool
    {
        return $this->is_bot;
    }

    public function is_curl(): bool
    {
        return $this->is_curl;
    }

    public function is_named_user_agent(): bool
    {
        foreach ($this->named_user_agents as $namedAgent) {
            if (stripos($this->user_agent, $namedAgent) !== false) {
                return true;
            }
        }
        return false;
    }

    public function handle_user_agent(): void
    {
        if ($this->is_bot()) {
            echo "Bot detected: " . $this->user_agent;
        } elseif ($this->is_mobile()) {
            echo "Mobile device detected: " . $this->user_agent;
        } elseif ($this->is_desktop()) {
            echo "Desktop device detected: " . $this->user_agent;
        } elseif ($this->is_curl()) {
            echo "cURL request detected: " . $this->user_agent;
        } elseif ($this->is_named_user_agent()) {
            echo "Named user agent detected: " . $this->user_agent;
        } else {
            echo "Unknown device: " . $this->user_agent;
        }
    }

    public function blocked_named_user_agent(array $blocked_user_agents): bool
    {
        $this->named_user_agents = $blocked_user_agents;

        foreach ($this->named_user_agents as $namedAgent) {
            if (empty($namedAgent)) {
                continue;
            }
            $pattern = '/' . str_replace('\*', '.*', preg_quote($namedAgent, '/')) . '/i';

            if (preg_match($pattern, $this->user_agent)) {
                return true;
            }
        }

        return false;
    }


}