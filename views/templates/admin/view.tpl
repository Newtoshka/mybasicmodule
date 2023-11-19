<div class="panel">

    {foreach from=$data item=item key=key name=name}
        <h1>
            {$item.user_id}
        </h1>

        <p>
        {$item.comment}
        </p>
    {/foreach}

</div>