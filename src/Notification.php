<?php

/*
 * Notification.
 * Note. You must have a local SMTP server which listening on port 25 to get this work,
 * as you would have to for send() function.
 * 
 * @authot Wojciech Brozyna <wojciech.brozyna@gmail.com>
 */

namespace phpconsole;

class Notification {
    
    /* types */
    const SUCCESS = 1;
    const ERROR   = 2;
    const INFO    = 3;
    
    /**
     * Email address for notification
     * Set email address in your child class if you wish to receive notifications
     * 
     * @var string
     */
    private $email = null;
    
    /**
     * Notification subject (email subject)
     * 
     * @var string
     */
    private $subject = 'M-Commander Notification';
    
    /**
     * Message
     * 
     * @var string
     */
    private $message;
    
    /**
     * Message header
     * 
     * @var string
     */
    private $header;
    
    /**
     * Set email
     * 
     * @param string $email
     * @return Notification
     */
    public function setEmail($email)
    {
        $this->email = $email;   
        return $this;
    }
    
    /**
     * Set subject
     * 
     * @param string $subject
     * @return Notification
     */
    public function setSubject($subject) 
    {
        $this->subject = $subject;
        return $this;
    }
    
    /**
     * Set message
     * 
     * @param string $message
     * @return Notification
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
        
    /**
     * Send notification
     * 
     * @param int $type
     * @return bool
     */
    public function send($type)
    {
        switch($type) {
            case Notify::SUCCESS:
                return $this->sendSuccess();
            case Notify::ERROR:
                return $this->sendError();
            case Notify::INFO:
                return $this->sendInfo();
            default:
                return false;
        }   
    }
    
    /**
     * Send success message
     * 
     * @return bool
     */
    private function sendSuccess()
    {
        $this->header = "<div style=\" width: 100%; padding: 15px; background: #00cc00; color: yellow;\">SUCCESS</div>";           
        return $this->__send(); 
    }
    
    /**
     * Send error message
     * 
     * @return bool
     */
    private function sendError()
    {
        $this->header = "<div style=\" width: 100%; padding: 15px; background: #ff0000; color: white;\">ERROR</div>";           
        return $this->__send(); 
    }
    
    /**
     * Send info message
     * 
     * @return bool
     */
    private function sendInfo()
    {
        $this->header = "<div style=\" width: 100%; padding: 15px; background: ##0099ff; color: white;\">INFO</div>";
        return $this->__send(); 
    }
    
    /**
     * Send email to recipient
     * 
     * @return bool
     */
    private function __send()
    {
        $message = $this->header . '<br>' . nl2br($this->message);
        $transport = (new \Swift_SmtpTransport('localhost', 25));
        $mailer = new \Swift_Mailer($transport);
        $message = new \Swift_Message($this->subject);
        $message->setBody($message);
        $message->setTo([$this->email]);
        
        return $mailer->send($message) > 0 ? true : false;
    }
    
}
