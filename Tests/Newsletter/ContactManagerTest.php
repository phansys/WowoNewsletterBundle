<?php

namespace Wowo\NewsletterBundle\Tests\Newsletter;

use Wowo\NewsletterBundle\Newsletter\Model\ContactManager;
use Wowo\NewsletterBundle\Entity\Contact;

class ContactManagerTest extends AbstractManagerBase
{
    public function testFindContactToChooseForMailing()
    {
        $this->assertEquals(array(null => 'john  ()'),
            $this->getContactManager()->findContactToChooseForMailing());
    }

    public function testFindChoosenContactIdForMailing()
    {
        $mock = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
                     ->disableOriginalConstructor()
                     ->getMock();
                     
        $mock->expects($this->any())
                 ->method('getData')
                 ->will($this->returnValue(array('contacts' => array(2 => 'john'))));

        $this->assertEquals(array(2 => 'john'),
            $this->getContactManager()->findChoosenContactIdForMailing($mock));
    }

    protected function getContactManager()
    {
        return new ContactManager($this->getEmMock(), $this->getContainerMock(), 'aClass');
    }
}
