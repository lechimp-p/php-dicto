<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Definition\Variables;

class Files extends Variable {
    /**
     * @inheritdoc
     */
    public function explain($text) {
        $v = new Files();
        $v->setExplanation($text);
        return $v;
    }
}
