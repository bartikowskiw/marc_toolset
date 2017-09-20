<?php

declare( strict_types = 1 );
namespace Umlts\MarcToolset;

/**
 * Mask for MARC records
 */
class MarcMask {

    /**
     * @var string $tag
     *   Tag, PCRE
     */
    private $tag;

    /**
     * @var string $subfield
     *   Subfield, PCRE
     */
    private $subfield;

    /**
     * @var array $ind
     *   Indicators, PCRE
     */
    private $ind = [ '.', '.' ];

    /**
     * @var array $ind
     *   PCRE for the field content, complete expression with
     *   delimiters and flags
     */
    private $regexp;


    /**
     * @var bool
     *   Saves if checking for the mask should be inverted.
     */
    private $invert = false;

    /**
     * Constructor.
     *
     * Leaving all params blank leads to a mask that is matching all
     * records.
     *
     * @var string $tag
     * @var string $ind1
     * @var string $ind2
     * @var string $subfield
     * @var string $regexp
     */
    public function __construct(
      string $tag = '...',
      string $ind1 = '.',
      string $ind2 = '.',
      string $subfield = '.',
      string $regexp = '.*' ) {

        $this
            ->setTag( $tag )
            ->setInd( $ind1, $ind2 )
            ->setSubfield( $subfield )
            ->setRegexp( $regexp );
    }

    /**
     * @var string $tag
     *   PCRE for the tag value
     * @return self
     */
    public function setTag( string $tag ) : self {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return string
     *   Returns tag PCRE
     */
    public function getTag() : string { return $this->tag; }

    /**
     * @var string ind1
     *   PCRE for second indicator's value
     * @var string ind2
     *   PCRE for second indicator's value
     * @return self
     */
    public function setInd( string $ind1, string $ind2 ) : self {
        $this->ind[0] = $ind1;
        $this->ind[1] = $ind2;
        return $this;
    }

    /**
     * @return array
     *   Returns first and second indicator PCRE as array
     */
    public function getInd() { return $this->ind; }

    /**
     * @return string
     *   Returns second indicator PCRE
     */
    public function getInd1() { return $this->ind[0]; }

    /**
     * @return string
     *   Returns first indicator PCRE
     */
    public function getInd2() { return $this->ind[1]; }

    /**
     * @var string $subfield
     *   PCRE for the subfield value
     * @return self
     */
    public function setSubfield( string $subfield ) : self {
        $this->subfield = $subfield;
        return $this;
    }

    /**
     * @return string
     *   Returns subfield PCRE
     */
    public function getSubfield() : string { return $this->subfield; }

    /**
     * @var string $string
     * @var string $flags
     *   Flags. Valid values: i, m, s, x, S, U, X, J, u.
     *   'e' is forbidden and not supported by PHP >= 7.0 anyways.
     * @see https://secure.php.net/manual/en/reference.pcre.pattern.modifiers.php
     * @return self
     * @throws RuntimeException
     *   When flags are not valid.
     */
    public function setRegexp( string $regexp, string $flags = 'i' ) : self {
        if ( !preg_match( '/^[imsxSUXJu]*$/', $flags ) ) {
            throw new \RuntimeException( 'PCRE flags not valid! See https://secure.php.net/manual/en/reference.pcre.pattern.modifiers.php.' );
        }
        $this->regexp = '/' . $regexp . '/' . $flags;
        return $this;
    }

    /**
     * @return string
     *   Returns the PCRE for the fields. Includes delimiter and
     *   flags!
     */
    public function getRegexp() : string { return $this->regexp; }

    /**
     * @var bool $invert
     * @return self
     */
    public function setInvert( bool $invert ) : self {
        $this->invert = $invert;
        return $this;
    }

    /**
     * @return bool
     */
    public function getInvert() : bool {
        return $this->invert;
    }

}
