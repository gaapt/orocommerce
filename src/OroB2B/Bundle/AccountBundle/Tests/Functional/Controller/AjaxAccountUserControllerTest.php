<?php

namespace OroB2B\Bundle\AccountBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroB2B\Bundle\AccountBundle\Entity\AccountUser;
use OroB2B\Bundle\AccountBundle\Tests\Functional\DataFixtures\LoadAccountUserData;

/**
 * @dbIsolation
 */
class AjaxAccountUserControllerTest extends WebTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $this->loadFixtures(
            [
                'OroB2B\Bundle\AccountBundle\Tests\Functional\DataFixtures\LoadAccountUserRoleData'
            ]
        );
    }

    public function testGetAccountIdAction()
    {
        /** @var AccountUser $user */
        $user = $this->getUserRepository()->findOneBy(['email' => LoadAccountUserData::EMAIL]);

        $this->assertNotNull($user);

        $id = $user->getId();

        $this->client->request(
            'GET',
            $this->getUrl('orob2b_account_account_user_get_account', ['id' => $id])
        );

        $result = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($result, 200);

        $data = json_decode($result->getContent(), true);

        $this->assertArrayHasKey('accountId', $data);

        $accountId = $user->getAccount() ? $user->getAccount()->getId() : null;

        $this->assertEquals($data['accountId'], $accountId);
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getUserRepository()
    {
        return $this->getObjectManager()->getRepository('OroB2BAccountBundle:AccountUser');
    }
}
