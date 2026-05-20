<?php
$currentURL = $_SERVER['HTTP_HOST'];
?>

<div class="page-content">
    <div class="container-fluid">
        <?php the_content(); ?>
    </div>

    <div class="container-fluid" style="max-width: 1500px;">
        <div class="row mb-6">

            <div class="col-6 pl-0 pr-0">
                <select id="pdfDropdown1" style="height: 4%; width:40%" class="choices-select">
                    <option value="<?php echo "https://$currentURL/wp-content/uploads/2023/10/sample1.pdf"; ?>" selected>
                        Manuscript
                    </option>
                    <option value="<?php echo "https://$currentURL/wp-content/uploads/2023/10/sample2.pdf"; ?>">
                        Printed text
                    </option>
                    <option value="<?php echo "https://$currentURL/wp-content/uploads/2023/10/Philosophia-Sinica_Treatise-1_Final-2.pdf"; ?>">
                        Transcription
                    </option>

                </select>

                <div id="pdfDisplayArea1" class="mt-2">
                    <iframe 
                        src="<?php echo "https://$currentURL/wp-content/uploads/2023/10/sample1.pdf"; ?>"
                        width="100%"
                        height="850"
                        title="PDF Document">
                    </iframe>
                </div>

            </div>
            <div class="col-6 pl-0 pr-0">
                <select id="pdfDropdown2" style="height: 4%; width:40%" class="choices-select">
                    <option value="<?php echo "https://$currentURL/wp-content/uploads/2023/10/sample1.pdf"; ?>">
                        Manuscript
                    </option>
                    <option value="<?php echo "https://$currentURL/wp-content/uploads/2023/10/sample2.pdf"; ?>" selected>
                        Printed text
                    </option>
                    <option value="<?php echo "https://$currentURL/wp-content/uploads/2023/10/Philosophia-Sinica_Treatise-1_Final-2.pdf"; ?>">
                        Transcription
                    </option>
                </select>

                <div id="pdfDisplayArea2" class="mt-2">
                    <iframe 
                        src="<?php echo "https://$currentURL/wp-content/uploads/2023/10/sample2.pdf"; ?>"
                        width="100%"
                        height="850"
                        title="PDF Document View">
                    </iframe>
                </div>

            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
    const choices1 = new Choices('#pdfDropdown1', {
        itemSelectText: '',
        searchEnabled: false,
        searchChoices: false,
    });

    const choices2 = new Choices('#pdfDropdown2', {
        itemSelectText: '',
        searchEnabled: false,
        searchChoices: false,
    });

    document.getElementById('pdfDropdown1').addEventListener('change', function() {
        const selectedPdfUrl = this.value;
        const displayArea = document.getElementById('pdfDisplayArea1');
        displayArea.innerHTML = `<iframe src="${selectedPdfUrl}" width="100%" height="850"></iframe>`;
    });

    document.getElementById('pdfDropdown2').addEventListener('change', function() {
        const selectedPdfUrl = this.value;
        const displayArea = document.getElementById('pdfDisplayArea2');
        displayArea.innerHTML = `<iframe src="${selectedPdfUrl}" width="100%" height="850"></iframe>`;
    });
</script>