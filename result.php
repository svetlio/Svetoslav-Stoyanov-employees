<?php include 'header.php'; ?>
<?php
if (!$_SESSION['file_name']) {
  header('location: index.php');
  exit();
}

$file_name = $_SESSION['file_name'];
$target_file = './uploads/' . $file_name;
if (!file_exists($target_file)) {
  unset($_SESSION['file_name']);
  exit();
}

$row_num = 1;
$data = [];
$handle = fopen('./uploads/' . $file_name, "r"); // open the file
if (($handle) !== FALSE) {
  while (($data_src = fgetcsv($handle, 1000, ",")) !== FALSE) { // read a row
    $num = count($data_src); // read the columns count

    $row = [];
    for ($c = 0; $c < $num; $c++) { // go through each col from current row
      if ($row_num > 1) { // skip the first row, it is for labels
        // Replace NULLs with today date.
        if ($c == 3 && $data_src[3] == 'NULL') {
          $data_src[$c] = date('Y-m-d', time());
        }

        // Prepare temporary row.
        $row[] = $data_src[$c];
      }
    }

    if ($row_num > 1 && validateRow($row)) { // skip labels
      // Create temp data.
      $data[$data_src[1]][] = $row;
    }

    $row_num++;
  }

  fclose($handle);
}

foreach ($data as $project_id => $employees_data) {
  // Pairs on the same projects.
  $pairs = findPairs($employees_data);

  // Intersect date ranges.
  foreach ($pairs as $pair) {
    $intersection = getIntersection($pair[0][2], $pair[0][3], $pair[1][2], $pair[1][3]);
    $time_diff = $intersection['end'] - $intersection['start'];
    $days = floor($time_diff / (60 * 60 * 24));
    if ($intersection !== FALSE) {
      $valid_pairs[$pair[0][0] . '_' . $pair[1][0] . '_' . $project_id] = [
        'pair' => $pair,
        'project_id' => $project_id,
        'intersection' => $intersection,
        'days' => $days,
      ];
    }
  }
}

usort($valid_pairs, function ($a, $b) {
  return $b['days'] > $a['days'];
});

$first = current($valid_pairs);

function validateRow($row) {
  $ret = TRUE;
  if (strtotime($row[2]) === FALSE || strtotime($row[3]) === FALSE) {
    $ret = FALSE;
  }
  return $ret;
}

/**
 * Find all pairs in array.
 * @param $arr
 * @param $index
 * @param $result
 * @return array|mixed
 */
function findPairs($arr, $index = 0, $result = []) {
  if ($index < count($arr)) {
    $first = $arr[$index];

    for ($i = $index + 1; $i < count($arr); $i++) {
      $result[] = [$first, $arr[$i]];
    }

    return findPairs($arr, $index + 1, $result);
  }
  else {
    return $result;
  }
}

/**
 * Get date ranges intersection.
 *
 * @param $start_1
 * @param $end_1
 * @param $start_2
 * @param $end_2
 * @return array|false
 */
function getIntersection($start_1, $end_1, $start_2, $end_2)
{
  $start_1 = strtotime($start_1);
  $end_1 = strtotime($end_1);
  $start_2 = strtotime($start_2);
  $end_2 = strtotime($end_2);
  if ($start_2 > $end_1 || $start_1 > $end_2 || $end_1 < $start_1 || $end_2 < $start_2) {
    return false;
  }
  $start = $start_1 < $start_2 ? $start_2 : $start_1;
  $end = $end_1 < $end_2 ? $end_1 : $end_2;
  return array('start' => $start, 'end' => $end);
}
?>

<hr />
<p><?php print $first['pair'][0][0] . ', ' . $first['pair'][1][0] . ', ' . $first['days']; ?></p>

<table class="table table-primary table-bordered">
  <thead>
  <tr>
    <th>Employee ID #1</th>
    <th>Employee ID #2</th>
    <th>Project ID</th>
    <th>Days</th>
  </tr>
  </thead>
  <tbody class="table-striped table-hover">
  <?php foreach ($valid_pairs as $row_final) { ?>
    <tr>
      <td><?php print($row_final['pair'][0][0]); ?></td>
      <td><?php print($row_final['pair'][1][0]); ?></td>
      <td><?php print($row_final['project_id']); ?></td>
      <td><?php print($row_final['days']); ?></td>
    </tr>
  <?php } ?>
  </tbody>
</table>

<?php include 'footer.php'; ?>
