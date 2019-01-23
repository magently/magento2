<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    private const METHOD_CODE = 'authorizenet_acceptjs';

    /**
     * @var Config
     */
    private $model;

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Config::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'methodCode' => self::METHOD_CODE,
            ]
        );
    }

    /**
     * @param $getterName
     * @param $configField
     * @param $configValue
     * @param $expectedValue
     * @dataProvider configMapProvider
     */
    public function testConfigGetters($getterName, $configField, $configValue, $expectedValue)
    {
        $this->scopeConfigMock->method('getValue')
            ->with($this->getPath($configField), ScopeInterface::SCOPE_STORE, 123)
            ->willReturn($configValue);
        $this->assertEquals($expectedValue, $this->model->{$getterName}(123));
    }

    /**
     * @dataProvider environmentUrlProvider
     * @param $environment
     * @param $expectedUrl
     */
    public function testGetApiUrl($environment, $expectedUrl)
    {
        $this->scopeConfigMock->method('getValue')
            ->with($this->getPath('environment'), ScopeInterface::SCOPE_STORE, 123)
            ->willReturn($environment);
        $this->assertEquals($expectedUrl, $this->model->getApiUrl(123));
    }

    /**
     * @dataProvider environmentSolutionProvider
     * @param $environment
     * @param $expectedSolution
     */
    public function testGetSolutionIdSandbox($environment, $expectedSolution)
    {
        $this->scopeConfigMock->method('getValue')
            ->with($this->getPath('environment'), ScopeInterface::SCOPE_STORE, 123)
            ->willReturn($environment);
        $this->assertEquals($expectedSolution, $this->model->getSolutionId(123));
    }

    public function configMapProvider()
    {
        return [
            ['getLoginId', 'login', 'username', 'username'],
            ['getTransactionKey', 'trans_key', 'password', 'password'],
            ['getLegacyTransactionHash', 'trans_md5', 'abc123', 'abc123'],
            ['getTransactionSignatureKey', 'trans_signature_key', 'abc123', 'abc123'],
            ['getPaymentAction', 'payment_action', 'authorize', 'authorize'],
            ['shouldEmailCustomer', 'email_customer', true, true],
            ['getAdditionalInfoKeys', 'paymentInfoKeys', 'a,b,c', ['a', 'b', 'c']],
        ];
    }
    public function environmentUrlProvider()
    {
        return [
            ['sandbox', 'https://apitest.authorize.net/xml/v1/request.api'],
            ['production', 'https://api.authorize.net/xml/v1/request.api'],
        ];
    }

    public function environmentSolutionProvider()
    {
        return [
            ['sandbox', 'AAA102993'],
            ['production', 'AAA175350'],
        ];
    }

    /**
     * Return config path
     *
     * @param string $field
     * @return string
     */
    private function getPath($field)
    {
        return sprintf(Config::DEFAULT_PATH_PATTERN, self::METHOD_CODE, $field);
    }
}
