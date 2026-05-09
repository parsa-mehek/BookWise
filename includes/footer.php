</div>

<div class="footer">
  <p>&copy; 2026 BookReview</p>
</div>

<script>
function togglePass(id) {
  const input = document.getElementById(id);
  if (!input) return;
  input.type = input.type === 'password' ? 'text' : 'password';
}

function toggleProfileMenu() {
  const menu = document.getElementById('profileMenu');
  if (menu) {
    menu.classList.toggle('active');
  }
}

// Close profile menu when clicking outside
document.addEventListener('click', function(event) {
  const profileDropdown = document.querySelector('.profile-dropdown');
  const profileMenu = document.getElementById('profileMenu');
  
  if (profileDropdown && !profileDropdown.contains(event.target)) {
    if (profileMenu) {
      profileMenu.classList.remove('active');
    }
  }
});
</script>
</body>
</html>
