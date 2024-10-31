<?php

declare (strict_types=1);
namespace DWS_QRWC_Deps\DI\Definition\Source;

use DWS_QRWC_Deps\DI\Definition\Definition;
/**
 * Describes a definition source to which we can add new definitions.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface MutableDefinitionSource extends DefinitionSource
{
    public function addDefinition(Definition $definition);
}
