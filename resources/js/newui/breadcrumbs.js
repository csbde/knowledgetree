jQuery(document).ready(function(){
    jQuery("#breadcrumb").jBreadCrumb({
        timeExpansionAnimation: 200,
        timeCompressionAnimation: 1000,
        timeInitialCollapse: 100,
        beginingElementsToLeaveOpen: 1,
        endElementsToLeaveOpen: 3,
        previewWidth: 10
    });
})