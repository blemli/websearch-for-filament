<?php

namespace Blemli\WebSearch\Enums;

/**
 * Image licenses, harmonised across engines. Engines that cannot express a
 * case fall back to the closest one they offer.
 */
enum License: string
{
    case Any = 'any';
    case PublicDomain = 'public_domain';
    case CreativeCommons = 'creative_commons';
    case Commercial = 'commercial';
    case Modify = 'modify';
    case ModifyCommercially = 'modify_commercially';
}
