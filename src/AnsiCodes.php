<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

/**
 * ANSI control strings
 */
class AnsiCodes {

    const red = "\e[31m";
    const yellow = "\e[33m";
    const magenta = "\e[95m";

    const bold = "\e[1m";
    const dim = "\e[2m";
    const underline = "\e[4m";
    const negative = "\e[7m";
    const reset = "\e[0m";
}
