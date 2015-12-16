<?php

namespace Oro\Bundle\ActionBundle\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

use Oro\Bundle\ActionBundle\Model\ActionDefinition;

class ActionDefinitionConfiguration implements ConfigurationInterface
{
    /**
     * @param array $configs
     * @return array
     */
    public function processConfiguration(array $configs)
    {
        $processor = new Processor();
        return $processor->processConfiguration($this, $configs);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root('action');
        $this->addNodes($root);

        return $builder;
    }

    /**
     * @param NodeDefinition $nodeDefinition
     * @return NodeDefinition
     */
    public function addNodes(NodeDefinition $nodeDefinition)
    {
        $nodeDefinition
            ->children()
                ->scalarNode('label')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('applications')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('entities')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('routes')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->variableNode('acl_resource')
                ->end()
                ->integerNode('order')
                    ->defaultValue(0)
                ->end()
                ->booleanNode('enabled')
                    ->defaultTrue()
                ->end()
                ->append($this->getAttributesNode())
                ->append($this->getFrontendOptionsNode())
                ->append($this->getFormOptionsNode())
            ->end();

        $this->appendFunctionsNodes($nodeDefinition->children());
        $this->appendConditionsNodes($nodeDefinition->children());

        return $nodeDefinition;
    }

    /**
     * @param NodeBuilder $builder
     */
    protected function appendFunctionsNodes(NodeBuilder $builder)
    {
        foreach (ActionDefinition::getAllowedFunctions() as $nodeName) {
            $builder
                ->arrayNode($nodeName)
                    ->prototype('variable')
                    ->end()
                ->end();
        }
    }

    /**
     * @param NodeBuilder $builder
     */
    protected function appendConditionsNodes(NodeBuilder $builder)
    {
        foreach (ActionDefinition::getAllowedConditions() as $nodeName) {
            $builder
                ->arrayNode($nodeName)
                    ->prototype('variable')
                    ->end()
                ->end();
        }
    }

    /**
     * @return NodeDefinition
     */
    protected function getAttributesNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('attributes');
        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('name')
                        ->cannotBeEmpty()
                    ->end()
                    ->enumNode('type')
                        ->defaultNull()
                        ->values(['bool', 'boolean', 'int', 'integer', 'float', 'string', 'array', 'object', 'entity'])
                    ->end()
                    ->scalarNode('label')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('property_path')
                        ->defaultNull()
                    ->end()
                    ->arrayNode('entity_acl')
                    ->end()
                    ->arrayNode('options')
                        ->prototype('variable')
                        ->end()
                    ->end()
                ->end()
                ->validate()
                    ->always(function ($config) {
                        $this->checkEntityAcl($config);
                        $this->checkOptionClass($config, in_array($config['type'], ['object', 'entity'], true));
                        $this->checkPropertyPath($config);

                        return $config;
                    })
                ->end()
            ->end();

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    protected function getFrontendOptionsNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('frontend_options');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('icon')->end()
                ->scalarNode('class')->end()
                ->scalarNode('group')->end()
                ->scalarNode('template')->end()
                ->scalarNode('dialog_title')->end()
                ->arrayNode('dialog_options')
                    ->prototype('variable')
                    ->end()
                ->end()
                ->scalarNode('dialog_template')->end()
            ->end();

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    protected function getFormOptionsNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('form_options');
        $node
            ->children()
                ->arrayNode('attribute_fields')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('form_type')->end()
                            ->arrayNode('options')
                                ->prototype('variable')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('attribute_default_values')
                    ->useAttributeAsKey('name')
                    ->prototype('variable')
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * @param array $config
     * @throws \Exception
     */
    protected function checkEntityAcl(array $config)
    {
        if ($config['type'] !== 'entity' && array_key_exists('entity_acl', $config)) {
            throw new \Exception(sprintf(
                'Attribute "%s" with type "%s" can\'t have entity ACL',
                $config['label'],
                $config['type']
            ));
        }
    }

    /**
     * @param array $config
     * @param bool $require
     * @throws \Exception
     */
    protected function checkOptionClass(array $config, $require)
    {
        if ($require && empty($config['options']['class'])) {
            throw new \Exception(sprintf('Option "class" is required for "%s" type', $config['type']));
        } elseif (!$require && !empty($config['options']['class'])) {
            throw new \Exception(sprintf('Option "class" cannot be used for "%s" type', $config['type']));
        }
    }

    /**
     * @param array $config
     * @throws \Exception
     */
    protected function checkPropertyPath(array $config)
    {
        if (empty($config['property_path']) && empty($config['label'])) {
            throw new \Exception('Option "label" or "property_path" is required');
        }

        if (empty($config['property_path']) && empty($config['type'])) {
            throw new \Exception('Option "type" or "property_path" is required');
        }
    }
}