  <h1>TASK:
    Pair of employees who have worked together</h1>
  <p>Application that identifies the pair of employees who have worked
    together on common projects for the longest period of time.</p>

<form action="upload.php" method="post" enctype="multipart/form-data">
  <div class="mb-3">
    <div class="mb-3">
      <label for="fileToUpload" class="form-label">Select a csv file to upload</label>
      <input class="form-control" type="file" name="fileToUpload" id="fileToUpload" required>
    </div>
  </div>
  <button type="submit" class="btn btn-primary" name="submit">Upload</button>
</form>
