function msg(){
    alert("Redirect to new page")
    document.write("Hello, Welcome to chase bank")
}

function getConfirmation(){
    var sign_in=confirm("Sign in: ")
    if (sign_in==true){
        document.write("You are logged in as user: ");
        return true;
    }
    else{
        document.write("Failed login attempt");
        return false;
    }

}

var artPieces = document.querySelectorAll('.art-piece');
artPieces.forEach(function(piece) {
  piece.addEventListener('click', function() {
    var title = this.querySelector('h2').textContent;
    var description = this.querySelector('p').textContent;
    var imageSrc = this.querySelector('img').getAttribute('src');
    
    displayInfo(title, description, imageSrc);
  });
});

function displayInfo(title, description, imageSrc) {
  // code to display information about the art piece in a modal or pop-up
  console.log('Title:', title);
  console.log('Description:', description);
  console.log('Image:', imageSrc);
}

  