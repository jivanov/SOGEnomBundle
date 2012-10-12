<?php

/*
 * This file is part of the SOG/EnomBundle
 *
 * (c) Shane O'Grady <shane.ogrady@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SOG\EnomBundle\DependencyInjection;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     * @return treeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sog_enom');
        $rootNode
            ->beforeNormalization()
                ->ifTrue(function ($v) { 
                    return is_array($v) && array_key_exists('accounts', $v) && !array_key_exists('username', $v);     
                })
                ->then(function ($v) {
                    $account = array();
                    foreach (array('username', 'password') as $key) {
                        if (array_key_exists($key, $v['accounts'])) {
                            $account[$key] = $v['accounts'][$key];
                            unset($v['accounts'][$key]);
                        }
                    }

                    $v['default_account'] = isset($v['default_account']) ? (string) $v['default_account'] : 'default';
                    
                    if (sizeof($account) && !isset($v['accounts'][ $v['default_account'] ] )) {
                        $v['accounts'] = array($v['default_account'] => $account);
                    }

                    
                    if (!isset($v['accounts'][ $v['default_account'] ])) {
                        
                        if (sizeof($v['accounts']) !== 1) {
                            throw new InvalidConfigurationException("Invalid default_account for sog_enom.");
                        }
                        else {
                            $v['default_account'] = key($v['accounts']);
                        }
                    }
                    
                    return $v;

                })
            ->end()
            ->fixXmlConfig('account')
            ->append($this->getEnomAccountsNode())
            ->children()
                ->scalarNode('default_account')
            ->end()
            ->scalarNode('url')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->scalarNode('username')
            ->end()
            ->scalarNode('password')
            ->end();

        return $treeBuilder;
    }
    
    private function getEnomAccountsNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('accounts');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
