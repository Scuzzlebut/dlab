import Vue from 'vue'

const state = {
}

const getters = {
}


const actions = {
    downloadFromResponse: ({ commit, dispatch }, res) => {
        var contentDisposition = res.headers['content-disposition'];
        var contentType = res.headers['content-type'];
        if (contentType == 'application/json') {
            const jsonString = Buffer.from(res.data).toString('utf8')
            const parsedData = JSON.parse(jsonString)
            dispatch('handleResponseMessage', parsedData)
        }
        else {
            //var filename = contentDisposition.split('filename=')[1].split(';')[0]; // commentata perchè non funziona nel 100% dei casi come nel caso di: attachment; filename="Ricevuta_6.pdf"; filename*=UTF-8''Ricevuta_6.pdf perchè le doppie virgolette vengono tenute
            let filename = 'download'; // fallback in caso di headers malformati nella risposta.
            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/; // questa regex dovrebbe essere abbastanza rodata e generica
            const matches = filenameRegex.exec(contentDisposition);
            if (matches != null && matches[1]) {
                filename = matches[1].replace(/['"]/g, '');
            }

            var file = new Blob([res.data], { type: contentType });
            // For Internet Explorer and Edge
            if ('msSaveOrOpenBlob' in window.navigator) {
                window.navigator.msSaveOrOpenBlob(file, filename);
            }
            // For Firefox and Chrome
            else {
                // Bind blob on disk to ObjectURL
                var data = URL.createObjectURL(file);
                var a = document.createElement("a");
                a.style = "display: none";
                a.href = data;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                // For Firefox
                setTimeout(function () {
                    document.body.removeChild(a);
                    // Release resource on disk after triggering the download
                    window.URL.revokeObjectURL(data);
                }, 100);
            }
        }
    },
}

const mutations = {
    openFromResponse(state, res) {
        var contentDisposition = res.headers['content-disposition'];
        var contentType = res.headers['content-type'];

        //var filename = contentDisposition.split('filename=')[1].split(';')[0]; // commentata perchè non funziona nel 100% dei casi come nel caso di: attachment; filename="Ricevuta_6.pdf"; filename*=UTF-8''Ricevuta_6.pdf perchè le doppie virgolette vengono tenute
        let filename = 'download'; // fallback in caso di headers malformati nella risposta.
        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/; // questa regex dovrebbe essere abbastanza rodata e generica
        const matches = filenameRegex.exec(contentDisposition);
        if (matches != null && matches[1]) {
            filename = matches[1].replace(/['"]/g, '');
        }

        if (window.navigator && window.navigator.msSaveOrOpenBlob) {
            var file = new Blob([res.data], { type: contentType });
        }
        else {
            var file = new File([res.data], filename, { type: contentType });
        }
        // For Internet Explorer and Edge
        if ('msSaveOrOpenBlob' in window.navigator) {
            window.navigator.msSaveOrOpenBlob(file, filename);
        }
        // For Firefox and Chrome
        else {
            // Bind blob on disk to ObjectURL
            var data = URL.createObjectURL(file);
            var a = document.createElement("a");
            a.style = "display: none";
            a.href = data;
            a.target = "_blank"
            a.title = filename
            document.body.appendChild(a);
            a.click();
            // For Firefox
            setTimeout(function () {
                document.body.removeChild(a);
                // Release resource on disk after triggering the download
                window.URL.revokeObjectURL(data);
            }, 100);
        }
    }
}

export default {
    state,
    getters,
    actions,
    mutations,
}


