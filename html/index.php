<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Code Editor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 30px;
        }

        .card {
            border: none;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        textarea {
            font-family: monospace;
            height: 120px;
            resize: none;
        }

        .btn-run {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        .btn-run:hover {
            background-color: #0056b3;
        }

        iframe {
            border: 1px solid #ccc;
            width: 100%;
            height: 350px;
            background: white;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2 class="text-center mb-4">🚀 Live Code Editor</h2>

        <div class="row">
            <!-- Kolom Kiri: Editor -->
            <div class="col-md-6">
                <div class="card p-3">
                    <div class="mb-3">
                        <label class="fw-bold">HTML</label>
                        <textarea id="html-code" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">CSS</label>
                        <textarea id="css-code" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">JavaScript</label>
                        <textarea id="js-code" class="form-control"></textarea>
                    </div>

                    <button class="btn btn-run w-100 mt-2" onclick="runCode()">Run Code</button>
                </div>
            </div>

            <!-- Kolom Kanan: Output -->
            <div class="col-md-6">
                <h3 class="text-center">🔍 Output:</h3>
                <div class="card p-3">
                    <iframe id="output"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script>
        function runCode() {
            let htmlCode = document.getElementById("html-code").value;
            let cssCode = "<style>" + document.getElementById("css-code").value + "</style>";
            let jsCode = "<script>" + document.getElementById("js-code").value + "<\/script>";

            let outputFrame = document.getElementById("output").contentWindow.document;
            outputFrame.open();
            outputFrame.write(htmlCode + cssCode + jsCode);
            outputFrame.close();
        }
    </script>

</body>

</html>