<?php

namespace Quatrevieux\Form\DataMapper\Generator;

use PHPUnit\Framework\TestCase;

class DataMapperClassTest extends TestCase
{
    public function test_code()
    {
        $generator = new DataMapperClass('MyDataMapper');

        $generator->setClassName('MyDto');
        $generator->setToDataObjectBody('return MyDto::create($data);');
        $generator->setToArrayBody('return $data->toArray();');

        $this->assertSame(<<<'PHP'
<?php

class MyDataMapper implements Quatrevieux\Form\DataMapper\DataMapperInterface
{
    function className(): string
    {
        return MyDto::class;
    }

    function toDataObject(array $fields): object
    {
        return MyDto::create($data);
    }

    function toArray(object $data): array
    {
        return $data->toArray();
    }
}

PHP
            , $generator->code());
    }
}
