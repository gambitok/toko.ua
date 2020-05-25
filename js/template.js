var activeProducts = JSON.parse($("#json_active_products").val());

var currentPageFilters = JSON.parse($("#json_filters").val());

var json_data = JSON.parse($("#json_active_filters").val());
var activeFilters = [];
Object.keys(json_data).forEach(function(key) {
    if (!Array.isArray(activeFilters[key])) activeFilters[key]=[];
    activeFilters[key]=json_data[key];
});

function initProductsForm(page) {
    let template_id=$("#template_id").val();
    let page_count=$("#select_count option:selected").val();
    activeProducts=JSON.stringify(activeProducts);
    JsHttpRequest.query(folder,{'w':'initProductsForm', 'activeFilters':activeFilters, 'activeProducts':activeProducts, 'currentPageFilters':currentPageFilters, 'page':page, 'page_count':page_count, 'template_id':template_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#template_products").html(result.content[0]);
            $("#template_filters").html(result.content[1]);
            activeProducts=result.content[2];
            window.history.pushState("catalogue", "Product", "?page="+page);
            new LazyLoad({ elements_selector: ".lazy" });

            navigateTo("result_target");
        }}, true);
}

function addFilterTemplate(param_id,value_id) {

    let template_id=$("#template_id").val();

    let statusFilters=1;

    if (!Array.isArray(activeFilters[param_id])) {
        activeFilters[param_id]=[];
    }

    let pos = extractKeyValue(activeFilters[param_id],value_id);

    if (pos!==undefined) {
        // remove filters
        activeFilters[param_id].splice(pos,1);
        statusFilters=0;
    } else {
        // add filters
        activeFilters[param_id].push(value_id);
        statusFilters=1;
    }

    let paramId=param_id;

    let status_empty=false;
    for (let i=0; i<activeFilters.length; i++) {
        if (!jQuery.isEmptyObject(activeFilters[i])) status_empty=true;
    }

    if (!status_empty) { clearFilters(); } else {
        activeProducts=JSON.stringify(activeProducts);
        JsHttpRequest.query(folder,{'w':'addFilterTemplate', 'statusFilters':statusFilters, 'paramId':paramId, 'activeFilters':activeFilters, 'activeProducts':activeProducts, 'currentPageFilters':currentPageFilters, 'template_id':template_id},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                $("#template_products").html(result.content[0]);
                $("#template_filters").html(result.content[1]);
                if (result.content[4]===1) {
                    clearFilters();
                } else {
                    activeProducts=result.content[2];
                    currentPageFilters=result.content[3];
                }

                window.history.pushState("catalogue", "Product", "/catalogue/filter/"+result.content[5]);

                setTimeout(hideLoader, 500);
                navigateTo("result_target");
            }}, true);
    }

}

function clearFilters() {
    let template_id=$("#template_id").val();
    JsHttpRequest.query(folder,{'w':'clearFilters', 'template_id':template_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            activeProducts=[];
            currentPageFilters=[];
            activeFilters=[];
            $("#template_products").html(result.content[0]);
            $("#template_filters").html(result.content[1]);
            window.history.pushState("catalogue", "Product", "/catalogue/filter/"+result.content[2]);
            navigateTo("result_target");
        }}, true);
}

function extractKeyValue(obj, value) {
    return Object.keys(obj)[Object.values(obj).indexOf(value)];
}

var stringConstructor = "test".constructor;
var arrayConstructor = [].constructor;
var objectConstructor = ({}).constructor;

function whatIsIt(object) {
    if (object === null) { return "null"; }
    if (object === undefined) { return "undefined"; }
    if (object.constructor === stringConstructor) { return "String"; }
    if (object.constructor === arrayConstructor) { return "Array"; }
    if (object.constructor === objectConstructor) { return "Object"; }
    { return "don't know"; }
}


