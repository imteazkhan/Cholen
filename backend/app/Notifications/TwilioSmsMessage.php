<?php

namespace App\Notifications;

class TwilioSmsMessage
{
    /**
     * The message content.
     *
     * @var string
     */
    public $content;

    /**
     * Set the message content.
     *
     * @param  string  $content
     * @return $this
     */
    public function content($content)
    {
        $this->content = $content;
        
        return $this;
    }

    /**
     * Get the message content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}