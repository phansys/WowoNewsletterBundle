<?php

namespace Wowo\NewsletterBundle\Tests\Newsletter;

use \Wowo\NewsletterBundle\Newsletter\Sender;

class SenderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $mailer = $this->getMockBuilder('\Swift_Mailer')
                     ->disableOriginalConstructor()
                     ->getMock();
        
        $builder = $this->getMockBuilder('\Wowo\NewsletterBundle\Newsletter\Builder')
                     ->disableOriginalConstructor()
                     ->getMock();
                     
        $spooler = new Sender($mailer, $builder);
    }
}
