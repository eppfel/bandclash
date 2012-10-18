<!-- Main hero unit for a primary marketing message or call to action -->
      <div class="hero-unit">
        <h1>The Bandclash Admin Panel</h1>
        <p>This is a prototype!</p>
        <p>In this stage you can crawl data about one band, show the aggregated data so far and export it to a triple file.
        <p>
          <form>
            <select name="uri">
              <option value="http://dbpedia.org/resource/The_Beatles">The Beatles</option>
              <option value="http://dbpedia.org/resource/The_Rolling_Stones">The Rolling Stones</option>
              <option value="http://dbpedia.org/resource/The_Who">The Who</option>
              <option value="http://www.bbc.co.uk/music/artists/b10bbbfc-cf9e-42e0-be17-e2c3e1d2600d#artist">The Beatles (@bbc)</option>
              <option value="http://rdf.freebase.com/ns/m.07c0j">The Beatles (@freebase)</option>
              <!-- Pink Floyd throws error by inserting uri "http://dbpedia.org/class/yago/PeopleFromDeal,Kent" <option value="http://dbpedia.org/resource/Pink_Floyd">Pink Floyd (@dbpedia)</option>-->
              <option value="http://www.bbc.co.uk/music/artists/83d91898-7763-47d7-b03b-b92132375c47#artist">Pink Floyd (@bbc)</option>
            </select>
            <label for="curib">
              <input type="checkbox" name="curib" /> Custom URI
              <input type="text" name="curi" />
            </label>
          </form>  
          <a class="btn btn-primary btn-large" id="crawlbtn">Crawl</a>
          <a class="btn btn-primary btn-large ajax" href="#print">Show Data</a>
          <a class="btn btn-primary btn-large" href="./server.php?action=export">Download Triples &raquo;</a>
          <a class="btn btn-primary btn-large ajax" href="#import">Import Triples</a>
          <a class="btn btn-danger btn-large ajax" href="#reset">Empty DB</a>
        </p>
      </div>
      <!-- Example row of columns -->
      <div class="row">
        <div id="output" class="span12">
        </div>
      </div>