<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateMethod;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Exception\TransformException;

class WorseGenerateMethodTest extends WorseTestCase
{
    /**
     * @dataProvider provideExtractMethod
     */
    public function testGenerateMethod(string $test, int $start, $name = null)
    {
        list($source, $expected) = $this->splitInitialAndExpectedSource(__DIR__ . '/fixtures/' . $test);

        $transformed = $this->generateMethod($source, $start, $name);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    public function provideExtractMethod()
    {
        return [
            'string' => [
                'generateMethod1.test',
                82,
            ],
            'parameter' => [
                'generateMethod2.test',
                82,
            ],
            'typed parameter' => [
                'generateMethod3.test',
                90,
            ],
            'undeclared parameter' => [
                'generateMethod4.test',
                79,
            ],
            'expression' => [
                'generateMethod5.test',
                225,
            ],
            'public accessor in another class' => [
                'generateMethod6.test',
                185,
            ],
        ];
    }

    public function testGenerateOnNonClassInterfaceException()
    {
        $this->expectException(TransformException::class);
        $this->expectExceptionMessage('Can only generate methods on classes');
        $source = <<<'EOT'
<?php 
interface Hello
{
}

class Goodbye
{
    /**
     * @var Hello
     */
    private $hello;

    public function greet()
    {
        $this->hello->asd();
    }
}
EOT
        ;

        $this->generateMethod($source, 156, 'test_name');
    }

    private function generateMethod(string $source, int $start, $name)
    {
        $generateMethod = new WorseGenerateMethod($this->reflectorFor($source), $this->updater());
        return $generateMethod->generateMethod(SourceCode::fromString($source), $start, $name);
    }
}

