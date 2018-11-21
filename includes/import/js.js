current_item_no = 0;
process = 0;

function start_process(import_process_items_not_processed)
{
   //show animation
   var loading_animation = document.getElementById("loading_animation");
   loading_animation.removeAttribute("hidden");
   
   process = 1;
   
   //disable start btn
   var start_btn = document.getElementById("start_btn");
   start_btn.setAttribute("disabled", "disabled");
   start_btn.removeAttribute("onclick");
   
   //enable pause btn
   var pause_btn = document.getElementById("pause_btn");
   pause_btn.onclick = function(){ pause_process(import_process_items_not_processed) };
   pause_btn.removeAttribute("disabled");
   
   //disable end btn
   var end_btn = document.getElementById("end_btn");
   end_btn.setAttribute("disabled", "disabled");
   end_btn.removeAttribute("onclick");
   
   process_product(import_process_items_not_processed);
}

function process_product(import_process_items_not_processed)
{
   //process product if process = 1
   if (process == 1)
   {
      var formData = new FormData();
      formData.append("asin", import_process_items_not_processed[current_item_no]["asin"]);
      
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '../includes/import/import_product.php', true);
      xhr.onload = function(e)
      {
         if (this.readyState === 4 & (this.status == 504 || this.status == 500))
         {
            process_product(import_process_items_not_processed);
         }
         else if (this.readyState === 4 & this.status != 200)
         {
            //hide animation
            var loading_animation = document.getElementById("loading_animation");
            loading_animation.setAttribute("hidden", "hidden");
            
            //show error (script error)
            swal({
                title: "Error",
                text: "There was an error with the import process. Please try again.",
                type: "error",
                showCancelButton: false,
                confirmButtonClass: 'btn-danger waves-effect waves-light',
                confirmButtonText: 'Ok'
            });
         }
         else if (this.readyState === 4 & this.status == 200)
         {
            var response = e.target.responseText;
            
            console.log(response);
            
            //if acceptable response
            if (response == "processed")
            {
               //update numbers
               current_item_no++;
               var span_items_processed = document.getElementById("span_items_processed");
               span_items_processed.innerHTML = Number(span_items_processed.innerHTML) + 1;
               
               //if more products left, repeat function
               if (current_item_no < import_process_items_not_processed.length)
               {
                  process_product(import_process_items_not_processed);
               }
               //if all products done
               else if (current_item_no >= import_process_items_not_processed.length)
               {
                  location.reload(true);
               }
            }
            //if cURL error
            else if (response.indexOf("cURL error") != -1)
            {
               process_product(import_process_items_not_processed);
            }
            //if ip ban error
            else if (response.indexOf("file_get_contents") != -1)
            {
               process_product(import_process_items_not_processed);
            }
            //if can't grab script element that contains images
            else if (response.indexOf("images_script_element") != -1)
            {
               process_product(import_process_items_not_processed);
            }
            //if can't grab category
            else if (response.indexOf("on line <b>247") != -1)
            {
               process_product(import_process_items_not_processed);
            }
            //if error
            else
            {
               //hide animation
               var loading_animation = document.getElementById("loading_animation");
               loading_animation.setAttribute("hidden", "hidden");
               
               //show error (script error)
               var error_box = document.getElementById("error_box");
               error_box.removeAttribute("hidden");
               error_box.innerHTML = response;
               
               swal({
                   title: "Error",
                   text: "There was an error with the import process. Please view page.",
                   type: "error",
                   showCancelButton: false,
                   confirmButtonClass: 'btn-danger waves-effect waves-light',
                   confirmButtonText: 'Ok'
               });
            }
         }
      }
      
      xhr.send(formData);
   }
}

function pause_process(import_process_items_not_processed)
{
   //hide animation
   var loading_animation = document.getElementById("loading_animation");
   loading_animation.setAttribute("hidden", "hidden");
   
   process = 0;
   
   //disable pause btn
   var pause_btn = document.getElementById("pause_btn");
   pause_btn.setAttribute("disabled", "disabled");
   pause_btn.removeAttribute("onclick");
   
   //enable start btn (change to 'continue')
   var start_btn = document.getElementById("start_btn");
   start_btn.onclick = function(){ start_process(import_process_items_not_processed) };
   start_btn.removeAttribute("disabled");
   start_btn.innerHTML = "Continue";
   
   //enable end btn
   var end_btn = document.getElementById("end_btn");
   end_btn.setAttribute("onclick", "end_process()");
   end_btn.removeAttribute("disabled");
}

function end_process()
{
   //change all titles to 'processed' and reload page
   var xhr = new XMLHttpRequest();
   xhr.open('POST', '../includes/import/end_process.php', true);
   xhr.onload = function(e)
   {
      if (this.readyState === 4 & this.status == 200)
      {
         var response = e.target.responseText;
         
         location.reload(true);
      }
   }
   xhr.send();
}