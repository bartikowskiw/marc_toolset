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
     * @return self
     */
    public function setRegexp( string $regexp, string $flags = 'i' ) : self {
        $this->regexp = '/' . $regexp . '/' . $flags;
        return $this;
    }

    public function getRegexp() { return $this->regexp; }

}
