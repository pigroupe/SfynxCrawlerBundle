<?php


$books = [
    '@attributes' => [
        'type' => 'fiction'
    ],
    'book' => [
        [
            '@attributes' => [
                'author' => 'George Orwell'
            ],
            'title' => '1984'
        ],
        [
            '@attributes' => [
                'author' => 'Isaac Asimov'
            ],
            'title' => ['@cdata'=>'Foundation'],
            'price' => '$15.61'
        ],
        [
            '@attributes' => [
                'author' => 'Robert A Heinlein'
            ],
            'title' =>  ['@cdata'=>'Stranger in a Strange Land'],
            'price' => [
                '@attributes' => [
                    'discount' => '10%'
                ],
                '@value' => '$18.00'
            ]
        ]
    ]
];

$xml = Data2XMLTransformer::build('books', $books);

/* creates
<books type="fiction">
  <book author="George Orwell">
    <title>1984</title>
  </book>
  <book author="Isaac Asimov">
    <title><![CDATA[Foundation]]></title>
    <price>$15.61</price>
  </book>
  <book author="Robert A Heinlein">
    <title><![CDATA[Stranger in a Strange Land]]</title>
    <price discount="10%">$18.00</price>
  </book>
</books>
*/