<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto as Dicto;
use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Rules;
use Lechimp\Dicto\Rules\Rule;
use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Variables as Vars;
use Lechimp\Dicto\Variables\Variable;
use Lechimp\Dicto\App\IndexDB;
use Lechimp\Dicto\Analysis\RulesToSqlCompiler;
use Lechimp\Dicto\Analysis\Violation;
use Lechimp\Dicto\Analysis\ReportGenerator;
use Doctrine\DBAL\DriverManager;
use Psr\Log\LogLevel;

require_once(__DIR__."/LoggerMock.php");

class ReportGeneratorMock implements ReportGenerator {
    public $violations = array();
    public function report_violation(Violation $violation) {
        $this->violations[] = $violation;
    }
    public function begin_ruleset(Ruleset $rule) {}
    public function end_ruleset(Ruleset $rule) {}
    public function begin_rule(Rule $rule) {}
    public function end_rule(Rule $rule) {}
}

// TODO: This seems to be a bad test, as it does not test Analyzer really.
// instead it tests if the rule works. This should be tested too, but tests
// need to be reorganized. This also might mean that Analyzer is not a good
// class.
class AnalyzerTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->connection = DriverManager::getConnection
            ( array
                ( "driver" => "pdo_sqlite"
                , "memory" => true
                )
            );
        $this->db = new IndexDB($this->connection);
        $this->db->init_sqlite_regexp();
        $this->db->maybe_init_database_schema();

        $this->rp = new ReportGeneratorMock();

        $this->log = new LoggerMock();
   }

    public function analyzer(Rules\Rule $rule) {
        $ruleset = new Ruleset($rule->variables(), array($rule));
        return new Dicto\Analysis\Analyzer($this->log, $ruleset, $this->db, $this->rp);
    }


    // All classes cannot contain text "foo".

    public function all_classes_cannot_contain_text_foo() {
        return new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\Classes("allClasses")
            , new Rules\ContainText()
            , array("foo")
            );
    }

    public function test_all_classes_cannot_contain_text_foo_1() {
        $rule = $this->all_classes_cannot_contain_text_foo();
        $analyzer = $this->analyzer($rule);

        $code = <<<CODE
1
2
3
foo
4
5
6
CODE;

        $this->db->source("file", $code);
        $this->db->definition("AClass", Variable::CLASS_TYPE, "file", 1, 7);

        $analyzer->run();
        $expected = array(new Violation
            ( $rule
            , "file"
            , 4
            , "foo"
            ));
        $this->assertEquals($expected, $this->rp->violations);
    }


    // All functions cannot depend on methods.

    public function all_functions_cannot_depend_on_methods() {
        return new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\Functions("allFunctions")
            , new Rules\DependOn()
            , array(new Vars\Methods("allMethods"))
            );
    }

    public function test_all_functions_cannot_depend_on_methods() {
        $rule = $this->all_functions_cannot_depend_on_methods();
        $analyzer = $this->analyzer($rule);

        $code = <<<CODE
1
2
3
foo
4
5
6
CODE;

        $this->db->source("file", $code);
        list($id1,$_) = $this->db->definition("a_function", Variable::FUNCTION_TYPE, "file", 1, 7);
        $id2 = $this->db->name("a_method", Variable::METHOD_TYPE);
        $this->db->relation($id1, $id2, "depend on", "file", 4);

        $analyzer->run();
        $expected = array(new Violation
            ( $rule
            , "file"
            , 4
            , "foo"
            ));
        $this->assertEquals($expected, $this->rp->violations);
    }

    public function test_logging() {
        $rule1 = new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\Classes("allClasses")
            , new Rules\ContainText()
            , array("foo")
            );
        $rule2 = new Rules\Rule
            ( Rules\Rule::MODE_CANNOT
            , new Vars\Functions("allFunctions")
            , new Rules\DependOn()
            , array(new Vars\Methods("allMethods"))
            );
        $vars = array_merge($rule1->variables(), $rule2->variables());

        $ruleset = new Ruleset($vars, array($rule1, $rule2));
        $analyzer = new Dicto\Analysis\Analyzer($this->log, $ruleset, $this->db, $this->rp);
        $analyzer->run();

        $expected = array(LogLevel::INFO, "checking: ".$rule1->pprint(), array());
        $this->assertContains($expected, $this->log->log);

        $expected = array(LogLevel::INFO, "checking: ".$rule2->pprint(), array());
        $this->assertContains($expected, $this->log->log);
    }
}
