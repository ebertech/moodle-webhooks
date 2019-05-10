<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/webhooks/externallib.php');
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

use local_webhooks\local\api;
use local_webhooks\local\record;

/**
 * Testing external functions.
 *
 * @copyright 2019 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class local_webhooks_external_testcase extends externallib_advanced_testcase {
    /**
     * Testing external get record's data.
     *
     * @throws \ReflectionException
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_response_exception
     */
    public function test_get_service() {
        self::setAdminUser();

        $this->resetAfterTest();

        $record = new record();
        $record->events = array_rand(api::get_events(), random_int(2, 10));
        $record->header = 'application/json';
        $record->name = 'Example name';
        $record->point = 'http://example.org/';
        $record->status = true;
        $record->token = generate_uuid();

        // Creating a new record.
        $record->id = api::create_service($record);

        $return = local_webhooks_external::get_service($record->id);
        $return = external_api::clean_returnvalue(local_webhooks_external::get_service_returns(), $return);
        self::assertInternalType('array', $return);

        // Testing the main fields.
        self::assertEquals($record->header, $return['header']);
        self::assertEquals($record->id, $return['id']);
        self::assertEquals($record->name, $return['name']);
        self::assertEquals($record->point, $return['point']);
        self::assertEquals($record->status, (int) $return['status']);
        self::assertEquals($record->token, $return['token']);

        // Testing an event's list.
        self::assertInternalType('array', $return['events']);
        self::assertNotCount(0, $return['events']);

        foreach ($return['events'] as $event) {
            self::assertContains($event, $record->events);
        }
    }
}