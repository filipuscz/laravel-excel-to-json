<!DOCTYPE html>
<html>
<head>
    <title>Excel to JSON Converter</title>
</head>
<body>
    <h1>CSV to JSON Converter</h1>
    <form action="/convert-excel-to-json" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="excel_file" required>
        <button type="submit">Convert</button>
    </form>
</body>
</html>
