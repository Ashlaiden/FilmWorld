<form role="search"
      method="get"
      action="<?php echo esc_url(home_url('/')); ?>">

    <input
        type="search"
        name="s"
        placeholder="جستجوی فیلم..."
        value="<?php echo esc_attr(get_search_query()); ?>"
    >

    <button type="submit">
        جستجو
    </button>

</form>