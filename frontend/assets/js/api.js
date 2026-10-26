window.sipekaAPI = {


  login: async function(username, password, role) {

    const response = await fetch('../../../backend/api/auth/login.php', {
      method: 'POST', 
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        username: username,
        password: password,
        role: role
      })
    });

    const data = await response.json();
    

    if (data.success && window.sipekaStore && data.user) {
      window.sipekaStore.setUser(data.user);
    }

    return data;
  }
};