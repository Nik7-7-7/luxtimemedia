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

th, td {
    max-width: 30rem;
}

td pre {
    max-width: 100%;
    white-space: pre-wrap;
    word-wrap: break-word;
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
	<h1>SSA revisions_meta</h1>
		<table>
			<thead>
					<tr>
            <?php foreach (array_keys($revisions_meta[0]) as $header): ?>
                <th>
                  <?php echo htmlspecialchars($header); ?>
                </th>
            <?php endforeach; ?>
					</tr>
			</thead>
			<tbody>
					<?php foreach ($revisions_meta as $item): ?>
					<tr>
							<?php foreach ($item as $value): ?>
									<td>
										<?php 
											if (is_array($value)) {
                        echo '<pre>';
                        echo htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT));
                        echo '</pre>';
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
