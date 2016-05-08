<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Definition\Fluid;
use Lechimp\Dicto\Definition\Variables as Vars;

/**
 * Provides fluid interface for with().
 */
class With extends Base {
    /**
     * Say to mean only artifacts whose name matches the provided regexp.
     *
     * @param   string  $regexp
     * @return  ExistingVar
     */
    public function name($regexp) {
        $subject = $this->rt->get_current_var();
        if (!($subject instanceof Vars\Variable)) {
            throw new \RuntimeException("Could not get current var from runtime.");
        }
        $this->rt->current_var_is(
            new Vars\WithName($regexp, $subject));

        return new ExistingVar($this->rt);
    }
}
