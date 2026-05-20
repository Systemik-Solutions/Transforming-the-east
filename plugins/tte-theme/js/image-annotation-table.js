jQuery(document).ready(function ($) {

    // Generate annotation images.
    $('.image-annotation-table').each(function () {
        const imageURL = $(this).data('imageUrl');
        const srcImageWidth = $(this).data('imageWidth');
        const srcImageHeight = $(this).data('imageHeight');

        if (imageURL) {
            const image = $(`<img crossOrigin="anonymous" src="${imageURL}">`);
            image.on('load', function () {
                const imageWidth = image[0].width;
                const cropper = new ImageCropper(image[0]);
                const ratio = imageWidth / srcImageWidth;
                $('.annotation-image-container').each(function () {
                    const area = $(this).data('area');
                    let segImgURL = null;
                    if (area.type === 'polygon') {
                        for (let i = 0; i < area.points.length; i++) {
                            area.points[i][0] = area.points[i][0] * ratio;
                            area.points[i][1] = area.points[i][1] * ratio;
                        }
                        segImgURL = cropper.crop(area.points);
                    } else if (area.type === 'circle') {
                        area.center[0] = area.center[0] * ratio;
                        area.center[1] = area.center[1] * ratio;
                        area.radius = area.radius * ratio;
                        segImgURL = cropper.cropCircle(area.center, area.radius);
                    }
                    if (segImgURL) {
                        $(this).append(`<img src="${segImgURL}">`);
                    }
                });
            });
        }
    });


});
