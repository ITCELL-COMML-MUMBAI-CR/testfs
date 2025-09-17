<?php
/**
 * Email Service for SAMPARK
 * Handles SMTP email sending that works on both XAMPP and Hostinger
 */

require_once __DIR__ . '/../config/Config.php';

class EmailService {

    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpEncryption;
    private $fromEmail;
    private $fromName;

    public function __construct() {
        $this->smtpHost = Config::SMTP_HOST;
        $this->smtpPort = Config::SMTP_PORT;
        $this->smtpUsername = Config::SMTP_USERNAME;
        $this->smtpPassword = Config::SMTP_PASSWORD;
        $this->smtpEncryption = Config::SMTP_ENCRYPTION;
        $this->fromEmail = Config::FROM_EMAIL;
        $this->fromName = Config::FROM_NAME;
    }

    /**
     * Send email using SMTP
     */
    public function sendEmail($to, $subject, $body, $isHtml = true) {
        try {
            // Create socket connection
            $smtp = $this->connectToSMTP();
            if (!$smtp) {
                return [
                    'success' => false,
                    'error' => 'Failed to connect to SMTP server'
                ];
            }

            // Send email commands
            $result = $this->sendSMTPCommands($smtp, $to, $subject, $body, $isHtml);

            // Close connection
            fclose($smtp);

            return $result;

        } catch (Exception $e) {
            error_log("EmailService Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Email error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Connect to SMTP server
     */
    private function connectToSMTP() {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        if ($this->smtpEncryption === 'ssl') {
            $smtp = stream_socket_client(
                "ssl://{$this->smtpHost}:{$this->smtpPort}",
                $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context
            );
        } else {
            $smtp = stream_socket_client(
                "tcp://{$this->smtpHost}:{$this->smtpPort}",
                $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context
            );
        }

        if (!$smtp) {
            error_log("SMTP connection failed: $errstr ($errno)");
            return false;
        }

        // Read greeting
        $response = fgets($smtp);
        if (substr($response, 0, 3) !== '220') {
            error_log("SMTP greeting failed: $response");
            fclose($smtp);
            return false;
        }

        return $smtp;
    }

    /**
     * Send SMTP commands
     */
    private function sendSMTPCommands($smtp, $to, $subject, $body, $isHtml) {
        try {
            // EHLO command
            fwrite($smtp, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
            $response = fgets($smtp);
            if (substr($response, 0, 3) !== '250') {
                return ['success' => false, 'error' => 'EHLO failed: ' . $response];
            }

            // Skip additional EHLO responses
            while (substr($response, 3, 1) === '-') {
                $response = fgets($smtp);
            }

            // Handle STARTTLS for TLS encryption
            if ($this->smtpEncryption === 'tls') {
                fwrite($smtp, "STARTTLS\r\n");
                $response = fgets($smtp);
                if (substr($response, 0, 3) !== '220') {
                    return ['success' => false, 'error' => 'STARTTLS failed: ' . $response];
                }

                // Enable TLS encryption on the stream
                $context = stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ]);

                if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    return ['success' => false, 'error' => 'Failed to enable TLS encryption'];
                }

                // Send EHLO again after TLS
                fwrite($smtp, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
                $response = fgets($smtp);
                if (substr($response, 0, 3) !== '250') {
                    return ['success' => false, 'error' => 'EHLO after TLS failed: ' . $response];
                }

                // Skip additional EHLO responses
                while (substr($response, 3, 1) === '-') {
                    $response = fgets($smtp);
                }
            }

            // AUTH LOGIN
            fwrite($smtp, "AUTH LOGIN\r\n");
            $response = fgets($smtp);
            if (substr($response, 0, 3) !== '334') {
                return ['success' => false, 'error' => 'AUTH LOGIN failed: ' . $response];
            }

            // Username
            fwrite($smtp, base64_encode($this->smtpUsername) . "\r\n");
            $response = fgets($smtp);
            if (substr($response, 0, 3) !== '334') {
                return ['success' => false, 'error' => 'Username failed: ' . $response];
            }

            // Password
            fwrite($smtp, base64_encode($this->smtpPassword) . "\r\n");
            $response = fgets($smtp);
            if (substr($response, 0, 3) !== '235') {
                return ['success' => false, 'error' => 'Password failed: ' . $response];
            }

            // MAIL FROM
            fwrite($smtp, "MAIL FROM: <{$this->fromEmail}>\r\n");
            $response = fgets($smtp);
            if (substr($response, 0, 3) !== '250') {
                return ['success' => false, 'error' => 'MAIL FROM failed: ' . $response];
            }

            // RCPT TO
            fwrite($smtp, "RCPT TO: <{$to}>\r\n");
            $response = fgets($smtp);
            if (substr($response, 0, 3) !== '250') {
                return ['success' => false, 'error' => 'RCPT TO failed: ' . $response];
            }

            // DATA
            fwrite($smtp, "DATA\r\n");
            $response = fgets($smtp);
            if (substr($response, 0, 3) !== '354') {
                return ['success' => false, 'error' => 'DATA failed: ' . $response];
            }

            // Email headers and body
            $headers = $this->buildHeaders($to, $subject, $isHtml);
            $encodedBody = quoted_printable_encode($body);
            $message = $headers . "\r\n\r\n" . $encodedBody . "\r\n.\r\n";

            // Debug logging
            error_log("Email Debug - To: $to, Subject: $subject");
            error_log("Email Debug - Body Length: " . strlen($body));
            error_log("Email Debug - Encoded Body Length: " . strlen($encodedBody));

            fwrite($smtp, $message);
            $response = fgets($smtp);
            if (substr($response, 0, 3) !== '250') {
                return ['success' => false, 'error' => 'Message send failed: ' . $response];
            }

            // QUIT
            fwrite($smtp, "QUIT\r\n");
            fgets($smtp);

            return ['success' => true, 'error' => null];

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'SMTP command error: ' . $e->getMessage()];
        }
    }

    /**
     * Build email headers
     */
    private function buildHeaders($to, $subject, $isHtml) {
        $headers = [];
        $headers[] = "From: {$this->fromName} <{$this->fromEmail}>";
        $headers[] = "To: {$to}";
        $headers[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";
        $headers[] = "Date: " . date('r');
        $headers[] = "Message-ID: <" . time() . "." . uniqid() . "@{$_SERVER['HTTP_HOST']}>";
        $headers[] = "MIME-Version: 1.0";

        if ($isHtml) {
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
        }

        $headers[] = "Content-Transfer-Encoding: quoted-printable";
        $headers[] = "X-Mailer: SAMPARK v" . Config::APP_VERSION;
        $headers[] = "X-Priority: 3";

        return implode("\r\n", $headers);
    }

    /**
     * Send bulk emails with throttling
     */
    public function sendBulkEmail($recipients, $subject, $body, $isHtml = true) {
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($recipients as $email) {
            $result = $this->sendEmail($email, $subject, $body, $isHtml);
            $results[] = [
                'email' => $email,
                'success' => $result['success'],
                'error' => $result['error']
            ];

            if ($result['success']) {
                $successCount++;
            } else {
                $failureCount++;
            }

            // Add delay to prevent overwhelming the server
            usleep(200000); // 0.2 second delay between emails
        }

        return [
            'success' => $successCount > 0,
            'total' => count($recipients),
            'successful' => $successCount,
            'failed' => $failureCount,
            'results' => $results
        ];
    }

    /**
     * Test SMTP connection
     */
    public function testConnection() {
        try {
            $smtp = $this->connectToSMTP();
            if (!$smtp) {
                return [
                    'success' => false,
                    'error' => 'Failed to connect to SMTP server'
                ];
            }

            // Test basic commands
            fwrite($smtp, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
            $response = fgets($smtp);

            // Skip additional EHLO responses
            while (substr($response, 3, 1) === '-') {
                $response = fgets($smtp);
            }

            // Test STARTTLS if needed
            if ($this->smtpEncryption === 'tls') {
                fwrite($smtp, "STARTTLS\r\n");
                $tlsResponse = fgets($smtp);
                if (substr($tlsResponse, 0, 3) !== '220') {
                    fclose($smtp);
                    return [
                        'success' => false,
                        'error' => 'STARTTLS test failed: ' . $tlsResponse
                    ];
                }

                // Enable TLS encryption on the stream
                if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    fclose($smtp);
                    return [
                        'success' => false,
                        'error' => 'Failed to enable TLS encryption during test'
                    ];
                }
            }

            fwrite($smtp, "QUIT\r\n");
            fgets($smtp);
            fclose($smtp);

            if (substr($response, 0, 3) !== '250') {
                return [
                    'success' => false,
                    'error' => 'SMTP handshake failed: ' . $response
                ];
            }

            return [
                'success' => true,
                'message' => 'SMTP connection successful'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }
}