<?php

namespace Wowo\NewsletterBundle\Tests\Newsletter;

use \Wowo\NewsletterBundle\Newsletter\Builder;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $MailingManager = $this->getMockBuilder('\Wowo\NewsletterBundle\Newsletter\Model\MailingManager')
                     ->disableOriginalConstructor()
                     ->getMock();

        $ContactManager = $this->getMockBuilder('\Wowo\NewsletterBundle\Newsletter\Model\ContactManager')
                     ->disableOriginalConstructor()
                     ->getMock();

        $PlaceholderProcessor = $this->getMockBuilder('\Wowo\NewsletterBundle\Newsletter\Placeholders\PlaceholderProcessor')
                     ->disableOriginalConstructor()
                     ->getMock();

        $MediaManager = $this->getMockBuilder('\Wowo\NewsletterBundle\Newsletter\Media\MediaManager')
                     ->disableOriginalConstructor()
                     ->getMock();

        $builder = new Builder($MailingManager, $ContactManager, $PlaceholderProcessor, $MediaManager);
    }
}
