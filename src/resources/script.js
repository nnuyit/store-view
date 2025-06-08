const handleFilter = (elm) => {
    const url = new URL(window.location.href);

    if (elm.value) {
        url.searchParams.set('range', elm.value); // Add or update filter
    } else {
        url.searchParams.delete('range'); // Remove filter if value is empty
    }
    window.location.href = url;
};
