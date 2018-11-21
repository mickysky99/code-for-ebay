total_pages = 0;
pages_done = 0;

function create_spy_process()
{
   var eBay_seller_ID = document.getElementById("eBay_seller_ID").value;
   
   if (eBay_seller_ID)
   {
      //show animation
      var loading_animation = document.getElementById("loading_animation");
      loading_animation.removeAttribute("hidden");
      
      //create spy process
      var formData = new FormData();
      formData.append("eBay_seller_ID", eBay_seller_ID);
      
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '../includes/spy/create_spy_process.php', true);
      xhr.onload = function(e)
      {
         if (this.readyState === 4 & this.status != 200)
         {
            //hide animation
            var loading_animation = document.getElementById("loading_animation");
            loading_animation.setAttribute("hidden", "hidden");
            
            //show error (script error)
            swal({
                title: "Error",
                text: "There was an error with the import process. Please view the console.",
                type: "error",
                showCancelButton: false,
                confirmButtonClass: 'btn-danger waves-effect waves-light',
                confirmButtonText: 'Ok'
            });
         }
         else if (this.readyState === 4 & this.status == 200)
         {
            var response = e.target.responseText;
            
            //if acceptable response
            if (Number(response) == 0)
            {
               //hide animation
               var loading_animation = document.getElementById("loading_animation");
               loading_animation.setAttribute("hidden", "hidden");
               
               //show notification
               swal({
                   title: "Error",
                   text: "No listings found!",
                   type: "error",
                   showCancelButton: false,
                   confirmButtonClass: 'btn-danger waves-effect waves-light',
                   confirmButtonText: 'Ok'
               });
            }
            else if (Number(response) > 0)
            {
               total_pages = Number(response);
               
               create_spy_process_2(eBay_seller_ID);
            }
            //if error
            else
            {
               //hide animation
               var loading_animation = document.getElementById("loading_animation");
               loading_animation.setAttribute("hidden", "hidden");
               
               //show error (script error)
               swal({
                   title: "Error",
                   text: "There was an error with the import process. Please view the console.",
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

function create_spy_process_2(eBay_seller_ID)
{
   var pages_processed_text = document.getElementById("pages_processed");
   var pages_total_text = document.getElementById("pages_total");
   var pages_heading_text = document.getElementById("pages_heading");
   
   pages_processed_text.innerHTML = pages_done + "/";
   pages_total_text.innerHTML = total_pages;
   pages_heading_text.innerHTML = "-Pages";
   
   var formData = new FormData();
   formData.append("eBay_seller_ID", eBay_seller_ID);
   formData.append("current_page", pages_done + 1);
   
   var xhr = new XMLHttpRequest();
   xhr.open('POST', '../includes/spy/create_spy_process_2.php', true);
   xhr.onload = function(e)
   {
      if (this.readyState === 4 & this.status != 200)
      {
         //hide animation
         var loading_animation = document.getElementById("loading_animation");
         loading_animation.setAttribute("hidden", "hidden");
         
         //show error (script error)
         swal({
             title: "Error",
             text: "There was an error with the import process. Please view the console.",
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
         if (Number(response) == 1)
         {
            pages_done += 1;
            pages_processed_text.innerHTML = pages_done + "/";
            
            //if more pages to process
            if (pages_done < total_pages && pages_done < 100)
            {
               create_spy_process_2(eBay_seller_ID);
            }
            else
            {
               location.reload(true);
            }
         }
         //if error
         else
         {
            //hide animation
            var loading_animation = document.getElementById("loading_animation");
            loading_animation.setAttribute("hidden", "hidden");
            
            //show error (script error)
            swal({
                title: "Error",
                text: "There was an error with the import process. Please view the console.",
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

spy_process_items_not_processed = "";
current_item_no = 0;
process = 0;

function start_process()
{
   //show animation
   var loading_animation = document.getElementById("loading_animation");
   loading_animation.removeAttribute("hidden");
   
   process = 1;
   
   //disable start btn
   var start_btn = document.getElementById("start_btn");
   start_btn.setAttribute("disabled", "disabled");
   start_btn.removeAttribute("onclick");
   
   //disable end btn
   var end_btn = document.getElementById("end_btn");
   end_btn.setAttribute("disabled", "disabled");
   end_btn.removeAttribute("onclick");
   
   //enable pause btn
   var pause_btn = document.getElementById("pause_btn");
   pause_btn.onclick = function(){ pause_process() };
   pause_btn.removeAttribute("disabled");
   
   //grab array of titles not processed
   var xhr = new XMLHttpRequest();
   xhr.open('POST', '../includes/spy/grab_titles_not_processed.php', true);
   xhr.onload = function(e)
   {
      if (this.readyState === 4 & this.status == 200)
      {
         var response = e.target.responseText;
         
         spy_process_items_not_processed = JSON.parse(response);
         
         console.log(spy_process_items_not_processed);
         
         //start process
         process_title();
      }
   }
   xhr.send();
}

function process_title()
{
   //process title if process = 1
   if (process == 1)
   {
      var formData = new FormData();
      formData.append("title_ID", spy_process_items_not_processed[current_item_no]["id"]);
      formData.append("title", spy_process_items_not_processed[current_item_no]["eBay_listing_title"]);
      formData.append("eBay_product_ID", spy_process_items_not_processed[current_item_no]["eBay_product_ID"]);
      
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '../includes/spy/run_process.php', true);
      xhr.onload = function(e)
      {
         if (this.readyState === 4 & (this.status == 504 || this.status == 500))
         {
            process_title(spy_process_items_not_processed);
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
            if (Number(response) == 1)
            {
               //update numbers
               current_item_no++;
               var titles_processed_text = document.getElementById("titles_processed");
               titles_processed_text.innerHTML = Number(titles_processed_text.innerHTML) + 1;
               
               //if more titles left, repeat function
               if (current_item_no < spy_process_items_not_processed.length)
               {
                  process_title(spy_process_items_not_processed);
               }
               //if all titles done
               else if (current_item_no >= spy_process_items_not_processed.length)
               {
                  location.reload(true);
               }
            }
            //if ip ban error
            else if (response.indexOf("file_get_contents") != -1)
            {
               process_title(spy_process_items_not_processed);
            }
            //if other error
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

function pause_process()
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
   start_btn.onclick = function(){ start_process() };
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
   xhr.open('POST', '../includes/spy/end_process.php', true);
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