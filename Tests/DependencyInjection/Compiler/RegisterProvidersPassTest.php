<?php

namespace Fazland\ElasticaBundle\Tests\DependencyInjection\Compiler;

use Fazland\ElasticaBundle\DependencyInjection\Compiler\RegisterProvidersPass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterProvidersPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessShouldRegisterTaggedProviders()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterProvidersPass();

        $registryDefinition = new Definition();

        $container->setDefinition('fazland_elastica.provider_registry', $registryDefinition);
        $container->setAlias('fazland_elastica.index', 'fazland_elastica.index.foo');

        $container->setDefinition('provider.foo.a', $this->createProviderDefinition(array('type' => 'a')));
        $container->setDefinition('provider.foo.b', $this->createProviderDefinition(array('index' => 'foo', 'type' => 'b')));
        $container->setDefinition('provider.bar.a', $this->createProviderDefinition(array('index' => 'bar', 'type' => 'a')));

        $pass->process($container);

        $calls = $registryDefinition->getMethodCalls();

        $this->assertEquals(array('addProvider', array('foo', 'a', 'provider.foo.a')), $calls[0]);
        $this->assertEquals(array('addProvider', array('foo', 'b', 'provider.foo.b')), $calls[1]);
        $this->assertEquals(array('addProvider', array('bar', 'a', 'provider.bar.a')), $calls[2]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProcessShouldRequireProviderImplementation()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterProvidersPass();

        $container->setDefinition('fazland_elastica.provider_registry', new Definition());
        $container->setAlias('fazland_elastica.index', 'fazland_elastica.index.foo');

        $providerDef = $this->createProviderDefinition();
        $providerDef->setClass('stdClass');

        $container->setDefinition('provider.foo.a', $providerDef);

        $pass->process($container);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProcessShouldRequireTypeAttribute()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterProvidersPass();

        $container->setDefinition('fazland_elastica.provider_registry', new Definition());
        $container->setAlias('fazland_elastica.index', 'fazland_elastica.index.foo');

        $container->setDefinition('provider.foo.a', $this->createProviderDefinition());

        $pass->process($container);
    }

    private function createProviderDefinition(array $attributes = array())
    {
        $provider = $this->getMockBuilder('Fazland\ElasticaBundle\Provider\ProviderInterface')->getMock();

        $definition = new Definition(get_class($provider));
        $definition->addTag('fazland_elastica.provider', $attributes);

        return $definition;
    }
}
