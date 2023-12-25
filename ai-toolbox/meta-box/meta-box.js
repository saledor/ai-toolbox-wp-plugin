jQuery(document).ready(function ($) {
    $("#ai_toolbox_h2_count").on("input", function () {
        $("#ai_toolbox_h2_count_val").text(this.value);
    });

    $("#ai_toolbox_h3_count").on("input", function () {
        $("#ai_toolbox_h3_count_val").text(this.value);
    });
});

jQuery(document).ready(function ($) {
    function blinkElement(selector, times, speed) {
        let count = 0;
        let blink = function () {
            $(selector).fadeOut(speed).fadeIn(speed, function () {
                count++;
                if (count < times) {
                    blink();
                }
            });
        };
        blink();
    }

    $("#ai_toolbox_generateBtn").click(function () {
        var directive = $("#ai_toolbox_directive").val();
        var h2_count = $("#ai_toolbox_h2_count").val();
        var h3_count = $("#ai_toolbox_h3_count").val();
        var seo_keywords = $("#ai_toolbox_seo_keywords").val();
        var seo_keywords_avoid = $("#ai_toolbox_seo_keywords_avoid").val();
        var seo_question = $("#ai_toolbox_seo_question").val();
        let min_allowed_input_length = 10;

        // Remove previous highlighting
        $("#ai_toolbox_directive, #ai_toolbox_seo_keywords, #ai_toolbox_seo_question").removeClass("is-invalid");

        // Check if at least one is filled
        if ((directive.length + seo_keywords.length + seo_question.length) < min_allowed_input_length) {
            $("#ai_toolbox_result").html("Please fill in either Content Directive, SEO Keywords, or SEO Question.").addClass("text-danger");
            $("#ai_toolbox_directive, #ai_toolbox_seo_keywords, #ai_toolbox_seo_question").addClass("is-invalid");
            return;
        }

        // Disable the Generate button
        $("#ai_toolbox_generateBtn").prop('disabled', true);

        // Clear result and show In progress...
        $("#ai_toolbox_result").html("In progress...").removeClass("text-danger").addClass("text-dark");

        // Show and initialize progress bar
        $("#ai_toolbox_progress").width("0%");
        $("#ai_toolbox_progressBar").show();
        var width = 1;
        var id = setInterval(frame, 2400);

        function frame() {
            if (width >= 100) {
                clearInterval(id);
            } else {
                width++;
                $("#ai_toolbox_progress").width(width + "%");
            }
        }

        var data = {
            action: "call_openai_api",
            nonce: ai_toolbox.ai_toolbox_meta_box_nonce,
            directive: directive,
            h2_count: h2_count,
            h3_count: h3_count,
            seo_keywords: seo_keywords,
            seo_keywords_avoid: seo_keywords_avoid,
            seo_question: seo_question
        };
        console.log("data", data);

        // Start the progress bar animation
        var progress = $("#ai_toolbox_progress");
        progress.width("0%");
        $("#ai_toolbox_progressBar").show();

        function animateProgressBar() {
            var width = progress.width();
            var parentWidth = progress.offsetParent().width();
            var percent = (100 * width) / parentWidth;
            if (percent < 100) {
                progress.width((percent + 1) + '%');
            }
        }
        // Store the interval ID so we can clear it later
        var progressInterval = setInterval(animateProgressBar, 2000);

        // Function to stop the progress bar
        function stopProgressBar() {
            clearInterval(progressInterval);
            $("#ai_toolbox_progressBar").hide();
        }

        $.ajax({
            url: ai_toolbox.ajax_url,
            type: 'POST',
            data: data,
            dataType: 'json'
        })
            .done(function (response) {
                // This will be called on a successful response (status 200)
                if (response.success) {
                    let taskId = response.data.task_id;
                    $("#ai_toolbox_result").html("Task ID: " + taskId);
                    checkTaskStatus(taskId);
                } else {
                    $("#ai_toolbox_result").html(response.data.error);
                }
            })
            .fail(function (jqXHR) {
                // This will be called on a failed response, such as 400 or 500 status codes
                resetTaskStatusChecker(); // No need to pass 'interval' since it's not set yet
                let message = "An error occurred.";
                if (jqXHR.status === 400) {
                    message = "Bad Request: " + jqXHR.responseText;
                } else if (jqXHR.status === 500) {
                    message = "Internal Server Error: " + jqXHR.responseText;
                }
                $("#ai_toolbox_result").html('<div class="alert alert-danger" role="alert">Error while making request: ' + message + '</div>');
            })

        function resetTaskStatusChecker(interval) {
            clearInterval(interval);
            stopProgressBar();
            $("#ai_toolbox_generateBtn").prop('disabled', false);
        }

        function checkTaskStatus(taskId) {
            let maxRetries = 150; // Set the maximum number of retries
            let retries = 0; // Initialize retries counter

            let interval = setInterval(function () {
                if (retries >= maxRetries) {
                    // If maximum retries reached, stop checking and show a message
                    resetTaskStatusChecker(interval);
                    $("#ai_toolbox_result").html('<div class="alert alert-danger" role="alert">No response received. Please try again.</div>');
                    return;
                }
                $.get(ai_toolbox.ajax_url, {
                    action: 'get_task_status',
                    task_id: taskId
                }, function (response) {
                    if (response.success) {
                        let taskStatus = response.data.status;
                        switch (taskStatus) {
                            case 'success':
                                resetTaskStatusChecker(interval);
                                $("#ai_toolbox_result").html('<div class="alert alert-info" role="alert">Task completed successfully.<br />' + response.data.data.suggestions + '</div>');
                                console.log(response.data);
                                wp.data.dispatch("core/editor").editPost({ title: response.data.data.title });

                                wp.data.dispatch('core/block-editor').clearSelectedBlock();
                                wp.data.dispatch('core/block-editor').resetBlocks([]);
                                insertContentBlock(response.data.data.content);
                                break;
                            case 'in_progress':
                                // Progress bar animation continues, no further action needed
                                break;
                            case 'error':
                                resetTaskStatusChecker(interval);
                                $("#ai_toolbox_result").html('<div class="alert alert-danger" role="alert">Error while making request: ' + response.data.message + '</div>');
                                break;
                        }
                    } else {
                        resetTaskStatusChecker(interval);
                        $("#ai_toolbox_result").html("<div class='alert alert-danger' role='alert'>Task failed: " + response.data.message + "</div>");
                    }
                }, 'json').fail(function (jqXHR, textStatus, errorThrown) {
                    resetTaskStatusChecker(interval);
                    let errorMsg = textStatus === 'timeout' ? "The request for task status timed out." :
                        textStatus === 'abort' ? "Task status check was aborted." :
                            textStatus === 'parsererror' ? "Failed to parse the server's response." :
                                "Server error occurred: " + (jqXHR.status ? jqXHR.status + " - " + jqXHR.statusText : errorThrown);

                    // Parse the JSON response and extract the data property
                    try {
                        let responseJson = JSON.parse(jqXHR.responseText);
                        if (responseJson && responseJson.data) {
                            errorMsg += "<br>Details: " + responseJson.data;
                        }
                    } catch (e) {
                        errorMsg += "<br>Details: Failed to parse the error response.";
                    }

                    $("#ai_toolbox_result").html('<div class="alert alert-danger" role="alert">' + errorMsg + '</div>');
                });

                retries++; // Increment the retries counter
            }, 2000); // Check every 2 seconds
        }


        // Function to insert content block
        function insertContentBlock(htmlContent) {
            try {
                const domParser = new DOMParser();
                const doc = domParser.parseFromString(htmlContent, "text/html");

                if (doc.body.childNodes.length === 0) {
                    const block = wp.blocks.createBlock("core/paragraph", {
                        content: htmlContent,
                    });
                    const insertedBlock = wp.data.dispatch('core/block-editor').insertBlocks(block);
                    wp.data.dispatch('core/block-editor').clearSelectedBlock();  // Clear any selected blocks

                    return;
                }

                doc.body.childNodes.forEach((node) => {
                    let block;

                    if (node.nodeName.startsWith("H")) {
                        const level = parseInt(node.nodeName.slice(1), 10);
                        block = wp.blocks.createBlock("core/heading", {
                            content: node.innerHTML,
                            level: level === 1 ? 2 : level // Downgrade h1 to h2, otherwise use as is
                        });
                    } else if (node.nodeName === "P") {
                        block = wp.blocks.createBlock("core/paragraph", {
                            content: node.innerHTML
                        });
                    } else if (node.nodeName === "UL" || node.nodeName === "OL") {
                        let innerBlocks = Array.from(node.querySelectorAll("li"))
                            .map((li) => wp.blocks.createBlock('core/list-item', { content: li.innerHTML }));

                        block = wp.blocks.createBlock('core/list', { ordered: node.nodeName === 'OL' }, innerBlocks);

                    } else {
                        if (node.outerHTML) {
                            block = wp.blocks.createBlock("core/html", {
                                content: node.outerHTML
                            });
                        }
                    }

                    if (block) {
                        const insertedBlock = wp.data.dispatch('core/block-editor').insertBlocks(block);
                        wp.data.dispatch('core/block-editor').clearSelectedBlock();  // Clear any selected blocks

                    }
                });
            } catch (error) {
                console.error('Error caught:', error);
                const block = wp.blocks.createBlock("core/paragraph", {
                    content: htmlContent,
                });
                const insertedBlock = wp.data.dispatch('core/block-editor').insertBlocks(block);
                wp.data.dispatch('core/block-editor').clearSelectedBlock();  // Clear any selected blocks

            }
        }

    });
});