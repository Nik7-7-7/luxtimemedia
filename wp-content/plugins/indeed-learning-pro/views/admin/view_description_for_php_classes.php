<?php
$data = [
    "UlpQuiz" => [
        [
          'method' => 'is_passed',
          'params' => '-',
          'return' => '1 or 0',
        ],
        [
          'method' => 'Grade',
          'params' => '-',
          'return' => 'integer or string',
        ],
        [
          'method' => 'has_grade',
          'params' => '-',
          'return' => 'integer or string',
        ],
    ],
    'UlpLesson' => [
      [
        'method' => 'is_completed',
        'params' => '-',
        'return' => 'true or false',
      ],
      [
        'method' => 'Duration',
        'params' => '-',
        'return' => 'integer',
      ],
    ],
];
?>
<table class="table">
    <thead class="thead-inverse">
        <tr>
            <td>Class</td>
            <td>Method</td>
            <td>Params</td>
            <td>Return Value</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $class => $methods):?>
            <?php foreach ($methods as $method_data):?>
                <tr>
                    <td><?php echo esc_html($class);?></td>
                    <td><?php echo esc_html($method_data['method']);?></td>
                    <td><?php echo esc_html($method_data['params']);?></td>
                    <td><?php echo esc_html($method_data['return']);?></td>
                </tr>
            <?php endforeach;?>
        <?php endforeach;?>
    </tbody>
</table>
