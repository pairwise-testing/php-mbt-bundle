<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Api;

class BugTest extends ApiTestCase
{
    public function testGetBugs()
    {
        $response = $this->makeApiRequest('GET', '/mbt/bugs');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString('
        [
            {
                "id": 1,
                "title": "Bug 1",
                "status": "unverified",
                "steps": "step1 step2 step3",
                "length": 3,
                "task": "/mbt/tasks/1",
                "bugMessage": "Something happen on shopping_cart model"
            },
            {
                "id": 2,
                "title": "Bug 2",
                "status": "valid",
                "steps": "step1 step2 step3 step4 step5",
                "length": 5,
                "task": "/mbt/tasks/1",
                "bugMessage": "We found a bug on shopping_cart model"
            },
            {
                "id": 3,
                "title": "Bug 3",
                "status": "valid",
                "steps": "step1 step2",
                "length": 2,
                "task": "/mbt/tasks/2",
                "bugMessage": "Weird bug when we test shoping_cart model"
             }
        ]', $response->getContent());
    }

    public function testCreateBug()
    {
        $response = $this->makeApiRequest('POST', '/mbt/bugs', '
        {
            "task": "/mbt/tasks/2",
            "bugMessage": "This bug never happen on task 2",
            "steps": "step1 step2 step3.1 step3.2",
            "length": 4,
            "title": "Bug 3",
            "status": "unverified"
        }');

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString('
        {
            "id": 4,
            "task": "/mbt/tasks/2",
            "bugMessage": "This bug never happen on task 2",
            "steps": "step1 step2 step3.1 step3.2",
            "length": 4,
            "title": "Bug 3",
            "status": "unverified"
        }', $response->getContent());
    }

    public function testCreateInvalidBug()
    {
        $response = $this->makeApiRequest('POST', '/mbt/bugs', '
        {
            "task": "/mbt/tasks/3",
            "title": "Bug 5",
            "status": "invalid-bug",
            "bugMessage": "This bug is invalid",
            "steps": "How to reproduce this bug?",
            "length": "invalid-length"
        }');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/problem+json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertArraySubset(json_decode('
        {
            "title": "An error occurred",
            "detail": "The type of the \"length\" attribute must be \"int\", \"string\" given."
        }', true), json_decode($response->getContent(), true));
    }
}
