<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Create Category</h1>
    <form method="post" action="/categories/store">
        <?= \Core\Csrf::input() ?>
        <div>
            <label>Name: <input name="name" required></label>
        </div>
        <div>
            <button type="submit">Create</button>
            <a href="/categories">Cancel</a>
        </div>
    </form>
</main>
