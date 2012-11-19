<?php

namespace Wowo\NewsletterBundle\Tests\Newsletter;

use lapistano\ProxyObject\ProxyBuilder;
use \Wowo\NewsletterBundle\Newsletter\Spooler;
use \Wowo\NewsletterBundle\Entity\Mailing;

class SpoolerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $queue = $this->getMockBuilder('\Wowo\QueueBundle\QueueManager')
                     ->disableOriginalConstructor()
                     ->getMock();

        $sender = $this->getMockBuilder('\Wowo\NewsletterBundle\Newsletter\Sender')
                     ->disableOriginalConstructor()
                     ->getMock();
        $spooler = new Spooler($queue, $sender);
        $spooler->setLogger(function() {});
    }

    public function testSpoolManyContacts()
    {
        $data = array('contactId' => 1, 'mailingId' => null, 'contactClass' => 'Foo');
        $queue = $this->getMockBuilder('\Wowo\QueueBundle\QueueManager')
                     ->disableOriginalConstructor()
                     ->getMock();
                     
        $queue->expects($this->once())
                 ->method('put')
                 ->with(json_encode((object)$data));
        $data['contactId'] = 2;
        $queue->expects($this->once())
                 ->method('put')
                 ->with(json_encode((object)$data));
        $sender = $this->getMockBuilder('\Wowo\NewsletterBundle\Newsletter\Sender')
                     ->disableOriginalConstructor()
                     ->getMock();
        $spooler = new Spooler($queue, $sender);

        $mailing= new Mailing();
        $mailing->setDelayedMailing(false);

        $count = $spooler->spoolManyContacts($mailing, array(1, 2), 'Foo');
        $this->assertEquals(2, $count);
    }

    public function testSpoolManyContactsNonUnique()
    {
        $data = array('contactId' => 1, 'mailingId' => null, 'contactClass' => 'Foo');
        $queue = $this->getMockBuilder('\Wowo\QueueBundle\QueueManager')
                     ->disableOriginalConstructor()
                     ->getMock();
        $queue->expects($this->once())
                 ->method('put')
                 ->with(json_encode((object)$data));
        $sender = $this->getMockBuilder('\Wowo\NewsletterBundle\Newsletter\Sender')
                     ->disableOriginalConstructor()
                     ->getMock();
        $spooler = new Spooler($queue, $sender);

        $mailing= new Mailing();
        $mailing->setDelayedMailing(false);

        $count = $spooler->spoolManyContacts($mailing, array(1,1), 'Foo');
        $this->assertEquals(1, $count);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSpoolManyContactsWithEmptyContacts()
    {
        $queue = $this->getMockBuilder('\Wowo\QueueBundle\QueueManager')
                     ->disableOriginalConstructor()
                     ->getMock();
        $sender = $this->getMockBuilder('\Wowo\NewsletterBundle\Newsletter\Sender')
                     ->disableOriginalConstructor()
                     ->getMock();
        $spooler = new Spooler($queue, $sender);

        $mailing= new Mailing();
        $mailing->setDelayedMailing(false);

        $count = $spooler->spoolManyContacts($mailing, array(), 'Foo');
    }

    public function testClearSuccessful()
    {
        $sender = $this->getMockBuilder('\Wowo\NewsletterBundle\Newsletter\Sender')
                     ->disableOriginalConstructor()
                     ->getMock();
        $queue = $this->getMockBuilder('\Wowo\QueueBundle\QueueManager')
                     ->disableOriginalConstructor()
                     ->getMock();
        $job = new \StdClass();
        $job->id = 666;

        $queue->expects($this->once())
                 ->method('get')
                 ->will($this->returnValue($job));

        $queue->expects($this->once())
                 ->method('delete')
                 ->with($job);
        $spooler = new Spooler($queue, $sender);

        $this->assertTrue($spooler->clear());
    }

    public function testClearNone()
    {
        $sender = $this->getMockBuilder('\Wowo\NewsletterBundle\Newsletter\Sender')
                     ->disableOriginalConstructor()
                     ->getMock();
        $queue = $this->getMockBuilder('\Wowo\QueueBundle\QueueManager')
                     ->disableOriginalConstructor()
                     ->getMock();

        $queue->expects($this->once())
                 ->method('get')
                 ->will($this->returnValue(null));
        $spooler = new Spooler($queue, $sender);

        $this->assertTrue(!$spooler->clear());
    }

    public function testGetInterval()
    {
        $proxy = new ProxyBuilder('\Wowo\NewsletterBundle\Newsletter\Spooler');
        $spooler = $proxy
            ->setMethods(array('getInterval'))
            ->disableOriginalConstructor()
            ->getProxy();

        $mailing = new Mailing();

        $mailing->setDelayedMailing(true);
        $mailing->setSendDate(new \DateTime('+1 second'));
        $this->assertEquals(1, $spooler->getInterval($mailing));
        $mailing->setSendDate(new \DateTime('+60 second'));
        $this->assertEquals(60, $spooler->getInterval($mailing));
        $mailing->setSendDate(new \DateTime('+1 minute'));
        $this->assertEquals(60, $spooler->getInterval($mailing));
        $mailing->setSendDate(new \DateTime('+1 hour'));
        $this->assertEquals(60*60, $spooler->getInterval($mailing));

        $mailing->setDelayedMailing(false);
        $this->assertNull($spooler->getInterval($mailing));
    }

    public function testProcess()
    {
        $mockJob = new MockJob();

        $queue = $this->getMockBuilder('\Wowo\QueueBundle\QueueManager')
                     ->disableOriginalConstructor()
                     ->getMock();

        $queue->expects($this->once())
                 ->method('get')
                 ->will($this->returnValue($mockJob));

        $queue->expects($this->once())
                 ->method('delete')
                 ->with($mockJob);

        $sender = $this->getMockBuilder('\Wowo\NewsletterBundle\Newsletter\Sender')
                     ->disableOriginalConstructor()
                     ->getMock();

        $sender->expects($this->once())
                 ->method('send')
                 ->with($mockJob->mailingId, $mockJob->contactId, $mockJob->contactClass)
                 ->will($this->returnValue(new MockMessage()));

        $spooler = new Spooler($queue, $sender);
        $spooler->setLogger(function() {});

        $spooler->process();
        $this->assertTrue(true, 'Everything should pass since there');

    }
}

class MockJob
{
    public $contactId = 1;
    public $mailingId = 1;
    public $contactClass = 'Foo';

    public function getData()
    {
        return json_encode($this);
    }
}

class MockMessage
{
    public function getTo()
    {
        return array('john@example.com' => 'john');
    }

    public function getSubject()
    {
        return 'foo bar';
    }
}
