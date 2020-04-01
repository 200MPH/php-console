<?php

/*
 * Notification
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
     * Set email
     * 
     * @param string $email
     */
    public function setEmail($email)
    {
        
        $this->email = $email;
        
    }
    
    /**
     * Set subject
     * 
     * @param string $subject
     */
    public function setSubject($subject) 
    {
        
        $this->subject = $subject;
        
    }
    
    /**
     * Set message
     * 
     * @param string $message
     */
    public function setMessage($message)
    {
        
        $this->message = $message;
        
    }
    
    /**
     * HTML message
     * 
     * @var string
     */
    private $html;
    
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
        
        $this->html = "<div style=\" width: 100%; padding: 15px; background: #00cc00; color: yellow;\">SUCCESS</div>";
                
        return $this->__send(); 
        
    }
    
    /**
     * Send error message
     * 
     * @return bool
     */
    private function sendError()
    {
        
        $this->html = "<div style=\" width: 100%; padding: 15px; background: #ff0000; color: white;\">ERROR</div>";
                
        return $this->__send(); 
        
    }
    
    /**
     * Send info message
     * 
     * @return bool
     */
    private function sendInfo()
    {
        
        $this->html = "<div style=\" width: 100%; padding: 15px; background: ##0099ff; color: white;\">INFO</div>";
                
        return $this->__send(); 
        
    }
    
    /**
     * Send email to receipient
     * 
     * @return bool
     */
    private function __send()
    {
      
        $message = $this->html . '<br>' . nl2br($this->message);
        
        $mail = \app\Core\MailComposer\MailComposerStrategy::create();
        $mail->addRecipient($this->email);
        $mail->setSubject($this->subject);
        $mail->setContent($message);
        
        return $mail->send() > 0 ? true : false;
        
    }
    
}
