<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Graph;

/**
 * A query on the IndexDB.
 */
interface IndexQuery extends Query {
    /**
     * Get files only.
     *
     * @return Query
     */
    public function files();

    /**
     * Get classes only.
     *
     * @return Query
     */
    public function classes();

    /**
     * Get methods only.
     *
     * @return Query
     */
    public function methods();

    /**
     * Get functions only.
     *
     * @return Query
     */
    public function functions();

    /**
     * Get globals only.
     *
     * @return Query
     */
    public function globals();

    /**
     * Get language constructs only.
     *
     * @return Query
     */
    public function language_constructs();

    /**
     * Expand to relations with given types.
     *
     * @param   string[]    $types
     * @return  Query
     */
    public function expand_relation(array $types);

    /**
     * Expand to the targets of the relations.
     *
     * @return  Query
     */
    public function expand_target();
}
