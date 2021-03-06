<?php

namespace OroB2B\Bundle\OrderBundle\Layout\Block\Extension;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Component\Layout\AbstractBlockTypeExtension;
use Oro\Component\Layout\Block\Type\BaseType;
use Oro\Component\Layout\BlockInterface;
use Oro\Component\Layout\BlockView;

class BlockPrefixExtension extends AbstractBlockTypeExtension
{
    /** {@inheritdoc} */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(['block_prefixes' => []])
            ->setAllowedTypes(['block_prefixes' => 'array']);
    }

    /** {@inheritdoc} */
    public function finishView(BlockView $view, BlockInterface $block, array $options)
    {
        $blockPrefixes = [];
        if (array_key_exists('block_prefixes', $view->vars)) {
            $blockPrefixes = (array)$view->vars['block_prefixes'];
        }
        if (array_key_exists('block_prefixes', $options)) {
            $blockPrefixes = array_merge($blockPrefixes, $options['block_prefixes']);
        }

        $view->vars['block_prefixes'] = $blockPrefixes;
    }

    /** {@inheritdoc} */
    public function getExtendedType()
    {
        return BaseType::NAME;
    }
}
