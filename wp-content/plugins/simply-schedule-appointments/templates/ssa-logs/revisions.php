<style>

.table-wrapper {
  font-family: monospace;
}
table {
    border-collapse: collapse;
    width: 100%;
}

table, th, td {
    border: 1px solid black;
}

thead th {
  background-color: #f5f5f5;
  position: sticky;
  top: 0;
  z-index: 10;
  box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
}

th, td {
    padding: 8px 12px;
}

th {
    background-color: #f2f2f2;
}
</style>

<div class="table-wrapper">
	<h1>SSA Revisions</h1>
		<table>
			<thead>
					<tr>
            <?php foreach (array_keys($revisions[0]) as $key => $header): ?>
                <th <?php if (strtolower($header) == "stack_trace") echo 'style="min-width: 150em;"'; ?> >
                  <?php echo htmlspecialchars($header); ?>
                </th>
            <?php endforeach; ?>
					</tr>
			</thead>
			<tbody>
					<?php foreach ($revisions as $item): ?>
					<tr>
							<?php foreach ($item as $key => $value): ?>
									<td>
										<?php 
											if($key == 'stack_trace'){
												$lines = explode('#', json_encode($value));
												$formattedOutput = "";

												foreach ($lines as $line) {
														if (trim($line) != '') {
																$formattedOutput .= "#" . $line . "\n";
														}
												}

												echo nl2br($formattedOutput);
											} else if (is_array($value)) {
													echo htmlspecialchars(json_encode($value));
											} else {
													echo htmlspecialchars($value);
											}
										?>
										</td>
							<?php endforeach; ?>
					</tr>
					<?php endforeach; ?>
			</tbody>
	</table>
</div>
