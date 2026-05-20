<?php

namespace Tests\Unit;

use App\Http\Requests\Admin\EmailTemplates\SendTestPostmarkRequest;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class SendTestPostmarkRequestTest extends TestCase
{
    public function testGetInvalidEmailListReturnsInvalidEmails(): void
    {
        $request = new SendTestPostmarkRequest();
        $method = new ReflectionMethod($request, 'getInvalidEmailList');
        $method->setAccessible(true);

        $invalidEmails = $method->invoke($request, 'ok@example.com, invalid-email, abc@@x');

        $this->assertSame(['invalid-email', 'abc@@x'], $invalidEmails);
    }

    public function testCcRuleClosureBuildsErrorMessageForInvalidList(): void
    {
        $request = new SendTestPostmarkRequest();
        $rules = $request->rules();
        $messages = [];

        foreach ($rules['cc'] as $rule) {
            if (is_callable($rule)) {
                $rule('cc', 'one@example.com,invalid-email', function ($message) use (&$messages) {
                    $messages[] = $message;
                });
            }
        }

        $this->assertNotEmpty($messages);
        $this->assertStringContainsString('invalid-email', $messages[0]);
    }
}
