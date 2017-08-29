<?php

declare( strict_types = 1 );
namespace Umlts\MarcToolset;

class MarcMask {

    private $tag;
    private $subfield;
    private $ind = [ '.', '.' ];
    private $regexp;

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

    public function setTag( string $tag ) : self {
        $this->tag = $tag;
        return $this;
    }

    public function getTag() : string { return $this->tag; }

    public function setInd( string $ind1, string $ind2 ) : self {
        $this->ind[0] = $ind1;
        $this->ind[1] = $ind2;
        return $this;
    }

    public function getInd() { return $this->ind; }
    public function getInd1() { return $this->ind[0]; }
    public function getInd2() { return $this->ind[1]; }

    public function setSubfield( string $subfield ) : self {
        $this->subfield = $subfield;
        return $this;
    }

    public function getSubfield() : string { return $this->subfield; }

    public function setRegexp( string $regexp, string $flags = 'i' ) : self {
        $this->regexp = '/' . $regexp . '/' . $flags;
        return $this;
    }

    public function getRegexp() { return $this->regexp; }

}
