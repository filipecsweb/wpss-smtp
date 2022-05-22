<?php
/**
 * Plugin Name:  SS - SMTP
 * Description:  Send email through SMTP when on production env and use MailHog when on localhost.
 * Version:      1.0.0
 * Author:       Filipe Seabra
 * Author URI:   https://filipeseabra.me
 * License:      GPLv3
 * License URI:  https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:  wpss-smtp
 */

namespace SsSmtp;

use PHPMailer\PHPMailer\PHPMailer;

class Smtp
{
    private static ?Smtp $instance = null;

    protected function __construct()
    {
        add_action('phpmailer_init', [__CLASS__, 'phpmailerInitCallback']);
    }

    /**
     * @param  PHPMailer  $phpmailer  Passed by reference.
     */
    public static function phpmailerInitCallback(PHPMailer $phpmailer)
    {
        self::setCredentials($phpmailer);
    }

    /**
     * @param  PHPMailer  $phpmailer
     */
    private static function setCredentials(PHPMailer $phpmailer)
    {
        if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {
            $phpmailer->isSMTP();
            $phpmailer->Host     = 'mailhog';
            $phpmailer->Port     = 1025;
            $phpmailer->From     = 'localhost@wordpress.com';
            $phpmailer->FromName = get_bloginfo('name');
        } else {
            $phpmailer->Mailer     = 'smtp';
            $phpmailer->Port       = 587;
            $phpmailer->SMTPAuth   = true;
            $phpmailer->SMTPSecure = 'tls';
            $phpmailer->FromName   = get_bloginfo('name');
            $phpmailer->From       = $_ENV['SMTP_FROM'] ?? '';
            $phpmailer->Host       = $_ENV['SMTP_HOST'] ?? '';
            $phpmailer->Username   = $_ENV['SMTP_USERNAME'] ?? '';
            $phpmailer->Password   = $_ENV['SMTP_PASSWORD'] ?? '';
        }

        $phpmailer->SMTPDebug = $_ENV['SMTP_DEBUG'] ?? 0;
    }

    /**
     * @return Smtp Class instance.
     */
    public static function getInstance(): ?Smtp
    {
        if ( ! (self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

add_action('plugins_loaded', ['SsSmtp\Smtp', 'getInstance']);
