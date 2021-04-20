<div class="container py-5">
    <h1 class="h1 mb-3 text-center text-primary">Эльдар Дадашов. Тестовое задание</h1>
    <form id="form-text">
        @csrf
        <div class="form-group mb-3">
            <label for="text">Текст:</label>
            <textarea name="text" placeholder="Enter your text" class="form-control" id="text" rows="3">
Some1 Some2 Some3 {Some1 Some2 Some3 Some4}
            </textarea>
        </div>
        <button type="submit" class="btn btn-primary text-light">Отправить</button>
    </form>
</div>

