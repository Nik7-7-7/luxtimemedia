<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Notification
{
    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var Quform_Form
     */
    protected $form;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param  array           $config
     * @param  Quform_Form     $form
     * @param  Quform_Options  $options
     */
    public function __construct(array $config, Quform_Form $form, Quform_Options $options)
    {
        $this->setConfig($config);
        $this->form = $form;
        $this->options = $options;
    }

    /**
     * Send the notification
     */
    public function send()
    {
        do_action('quform_pre_get_mailer_config', $this, $this->form);
        do_action('quform_pre_get_mailer_config_' . $this->getIdentifier(), $this, $this->form);

        $config = $this->getMailerConfig();

        do_action('quform_post_get_mailer_config', $this, $this->form);
        do_action('quform_post_get_mailer_config_' . $this->getIdentifier(), $this, $this->form);

        if (is_array($config)) {
            add_action('phpmailer_init', array($this, 'processHooks'));

            wp_mail($config['to'], $config['subject'], $config['message'], $config['headers'], $config['attachments']);

            remove_action('phpmailer_init', array($this, 'processHooks'));

            do_action('quform_post_send_notification', $this, $this->form);
            do_action('quform_post_send_notification_' . $this->getIdentifier(), $this, $this->form);
        }
    }

    /**
     * Replace variable tokens within the recipient fields.
     *
     * @param  array  $recipient
     * @return array
     */
    protected function replaceVariables(array $recipient)
    {
        $recipient['address'] = $this->form->replaceVariables($recipient['address']);
        $recipient['name'] = $this->form->replaceVariables($recipient['name']);

        return $recipient;
    }

    /**
     * Returns the config value for the given $key
     *
     * @param   string  $key
     * @param   null    $default
     * @return  mixed   The config value or $default if not set
     */
    public function config($key, $default = null)
    {
        $value = Quform::get($this->config, $key, $default);

        if ($value === null) {
            $value = Quform::get(call_user_func(array(get_class($this), 'getDefaultConfig')), $key, $default);
        }

        return $value;
    }

    /**
     * Set the config value for the given $key or multiple values using an array
     *
     * @param   string|array  $key    Key or array of key/values
     * @param   mixed         $value  Value or null if $key is array
     * @return  $this
     */
    public function setConfig($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->config[$k] = $v;
            }
        } else {
            $this->config[$key] = $value;
        }

        return $this;
    }

    /**
     * Get the parameters to pass to the mail function
     *
     * @return array|false The mailer config array or false if this notification should not be sent
     */
    public function getMailerConfig()
    {
        $config = array(
            'to' => array(),
            'headers' => array(),
            'subject' => $this->form->replaceVariables($this->config('subject')),
            'message' => '',
            'attachments' => array()
        );

        $recipients = array();
        $emailValidator = new Quform_Validator_Email();

        if ($this->config('conditional')) {
            foreach ($this->config('conditionals') as $conditional) {
                if (count($conditional['logicRules']) && $this->form->checkLogicAction($conditional['logicAction'], $conditional['logicMatch'], $conditional['logicRules'])) {
                    foreach ($conditional['recipients'] as $recipient) {
                        $recipient = $this->replaceVariables($recipient);
                        if ($emailValidator->isValid($recipient['address'])) {
                            $recipients[] = $recipient;
                        }
                    }
                }
            }

            if ( ! count($recipients)) {
                if ($this->config('conditionalFallback') == 'do_not_send') {
                    return false;
                } else {
                    foreach ($this->config('recipients') as $recipient) {
                        $recipient = $this->replaceVariables($recipient);
                        if ($emailValidator->isValid($recipient['address'])) {
                            $recipients[] = $recipient;
                        }
                    }
                }
            }
        } else {
            foreach ($this->config('recipients') as $recipient) {
                $recipient = $this->replaceVariables($recipient);
                if ($emailValidator->isValid($recipient['address'])) {
                    $recipients[] = $recipient;
                }
            }
        }

        foreach ($recipients as $recipient) {
            $formatted = $this->formatRecipient($recipient);

            switch ($recipient['type']) {
                case 'to':
                    $config['to'][] = $formatted;
                    break;
                case 'cc':
                    $config['headers'][] = 'Cc: ' . $formatted;
                    break;
                case 'bcc':
                    $config['headers'][] = 'Bcc: ' . $formatted;
                    break;
                case 'reply':
                    $config['headers'][] = 'Reply-to: ' . $formatted;
                    break;
            }
        }

        if (Quform::isNonEmptyString($this->config('from.address'))) {
            $from = $this->replaceVariables($this->config('from'));
            if ($emailValidator->isValid($from['address'])) {
                $config['headers'][] = 'From: ' . $this->formatRecipient($from);
            }
        }

        if ($this->config('format') == 'html' || $this->config('format') == 'multipart') {
            $config['headers'][] = sprintf('Content-type: text/html; charset=%s', apply_filters('wp_mail_charset', get_bloginfo('charset')));
            $config['message'] = $this->config('html');

            if ($this->config('autoFormat')) {
                $config['message'] = nl2br($config['message']);
            }

            $config['message'] = $this->form->replaceVariables($config['message'], 'html');

            if ($this->isRtl()) {
                $config['message'] = sprintf('<div dir="rtl">%s</div>', $config['message']);
            }

            $config['message'] = $this->wrapHtmlMessage($config['message']);
        } else {
            $config['message'] = $this->form->replaceVariables($this->config('text'));
        }

        foreach ($this->config('attachments') as $attachment) {
            if ($attachment['source'] == 'element') {
                $element = $this->form->getElementById($attachment['element']);

                if ($element instanceof Quform_Attachable && $element->hasAttachments()) {
                    foreach ($element->getAttachments() as $file) {
                        if (is_file($file)) {
                            $config['attachments'][] = $file;
                        }
                    }
                }
            } elseif ($attachment['source'] == 'media') {
                if (is_array($attachment['media'])) {
                    foreach ($attachment['media'] as $medium) {
                        $post = get_post($medium['id']);

                        if ($post instanceof WP_Post && $post->post_type == 'attachment') {
                            $file = get_attached_file($post->ID);

                            if (is_file($file)) {
                                $config['attachments'][] = $file;
                            }
                        }
                    }
                }
            }
        }

        $config = apply_filters('quform_notification_mailer_config', $config, $this, $this->form);
        $config = apply_filters('quform_notification_mailer_config_' . $this->getIdentifier(), $config, $this, $this->form);

        return $config;
    }

    /**
     * Is this notification RTL?
     *
     * @return bool
     */
    public function isRtl()
    {
        if ($this->config('rtl') == 'inherit') {
            return $this->form->isRtl();
        }

        return $this->config('rtl') == 'yes';
    }

    /**
     * Run hooks before the email is sent
     *
     * @param \PHPMailer\PHPMailer\PHPMailer|PHPMailer $mailer
     */
    public function processHooks($mailer)
    {
        do_action('quform_pre_send_notification', $mailer, $this, $this->form);
        do_action('quform_pre_send_notification_' . $this->getIdentifier(), $mailer, $this, $this->form);
    }

    /**
     * Format the given recipient for an email header
     *
     * @param   array   $recipient
     * @return  string
     */
    protected function formatRecipient(array $recipient)
    {
        if (Quform::isNonEmptyString($recipient['name'])) {
            $formatted = sprintf('%s <%s>', $recipient['name'], $recipient['address']);
        } else {
            $formatted = $recipient['address'];
        }

        return $formatted;
    }


    /**
     * Wraps the given message in outer HTML tags
     *
     * @param   string  $message
     * @return  string
     */
    protected function wrapHtmlMessage($message)
    {
        $start = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=%s" />
<title></title>
</head>
<body style="margin:0;padding:%s;">';

        $start = sprintf(
            $start,
            esc_attr(apply_filters('wp_mail_charset', get_bloginfo('charset'))),
            esc_attr(Quform::addCssUnit($this->config('padding')))
        );

        $start = apply_filters('quform_notification_html_start', $start, $this);
        $start = apply_filters('quform_notification_html_start_' . $this->getIdentifier(), $start, $this);

        $end = '</body></html>';

        $end = apply_filters('quform_notification_html_end', $end, $this);
        $end = apply_filters('quform_notification_html_end_' . $this->getIdentifier(), $end, $this);

        return $start . $message . $end;
    }

    /**
     * Get the notification unique ID
     *
     * @return string
     */
    public function getIdentifier()
    {
        return sprintf('%d_%d', $this->form->getId(), $this->config('id'));
    }

    /**
     * Add the AltBody to multipart email notifications
     *
     * Currently, this needs to be done with a hook as wp_mail() does not support multipart messages
     *
     * @param  \PHPMailer\PHPMailer\PHPMailer|PHPMailer $mailer        The PHPMailer instance
     * @param  Quform_Notification                      $notification  The notification instance
     * @param  Quform_Form                              $form          The form instance
     */
    public static function addAltBody($mailer, Quform_Notification $notification, Quform_Form $form)
    {
        if ($notification->config('format') == 'multipart') {
            $mailer->AltBody = $form->replaceVariables($notification->config('text'));
        } else {
            $mailer->AltBody = '';
        }
    }

    /**
     * Get the default notification configuration
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultConfig($key = null)
    {
        $config = apply_filters('quform_default_notification', array(
            'name' => '',
            'enabled' => true,
            /* translators: %s: the form name */
            'subject' => sprintf(__('New submission from %s', 'quform'), '{form_name}'),
            'format' => 'html',
            'html' => '{all_form_data}',
            'autoFormat' => true,
            'padding' => '20',
            'rtl' => 'inherit',
            'text' => '{all_form_data}',
            'recipients' => array(array('type' => 'to', 'address' => '{default_email_address}', 'name' => '{default_email_name}')),
            'conditional' => false,
            'conditionals' => array(),
            'conditionalFallback' => '',
            'from' => array('address' => '{default_from_email_address}', 'name' => '{default_from_email_name}'),
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),
            'attachments' => array()
        ));

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
