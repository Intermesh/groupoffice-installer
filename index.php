<?php
ob_end_clean();
ob_start();

	header('Content-type: text/html; charset=utf-8' );
	$startSetup = false;
	include_once('Installer.php');
	$setup = new Installer();
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link href="css/style.css" rel="stylesheet" type="text/css"/>
	</head>
	<body>

		<header>
			<h1>Group-Office</h1>
			<small>Installation</small>
			
		</header>

		<?php if($setup->isInstalled()): ?>

		<section>
			<fieldset>
				<h2>Group-Office is already installed</h2>
				<p>If you want to reinstall please (re)move your config.php file and use an empty database with an empty data folder</p>
			</fieldset>
		</section>

		<?php elseif(!$_POST): ?>
		<section>
			<form method="POST" action="">
			<fieldset>
				<h2>Create an admin account</h2>
				<label>
					<input type="text" name="admin_user" value="<?=$setup->admin_user;?>" placeholder="Username" required />
				</label>
				<label>
					<input type="password" name="admin_pass" value="<?=$setup->admin_pass;?>" placeholder="Password" required />
				</label>
			</fieldset>

			<fieldset>
				<h2>Data folder</h2>
				<label>
				<input type="text" name="data_folder" value="<?=$setup->data_folder;?>" required />
				</label>
			</fieldset>

			<fieldset>
				<h2>Configure the database</h2>
				<label>
					<input type="text" name="db_user" value="<?=$setup->db_user;?>" placeholder="Database user" required />
				</label>
				<label>
					<input type="password" name="db_pass" value="<?=$setup->db_pass;?>" placeholder="Database password"  />
				</label>
				<label>
					<input type="text" name="db_name" value="<?=$setup->db_name;?>" placeholder="Database name" required />
				</label>
				<label>
					<input type="text" name="db_host" value="<?=$setup->db_host;?>" placeholder="Database host" required />
				</label>
			</fieldset>

			<button>Start setup</button>
			</form>
		</section>

		<?php else: ?>
		<?php $tests = $setup->systemTests(); ?>
		<section>

			<fieldset>
				<h2>System check</h2>
				<ul class="tests">
				<?php foreach($tests as $test): ?>
					<?php if(!$test['pass']): if($test['fatal']) {$fatal = true;} ?>
					<li<?= $test['fatal']?' class="error"':''?>><b><?= $test['name'] . '</b><br><small>' . $test['feedback'];?></small></li>
					<?php endif; ?>
				<?php endforeach; ?>
				</ul>
			</fieldset>

		</section>
		<section>
			<?php if(empty($fatal)): $startSetup = true; ?>
				<script type="text/javascript">
					var loading = setInterval(function() { document.getElementById('process').textContent += ' .'; }, 100);
				</script>

				<fieldset>
					<h2>Process</h2>
					<span id="process">Installing Group-Office.</span>
				</fieldset>
				<button id="btnContinue" onclick="window.location.href = '../';" style="display:none;">Start Group-Office</button>
			
			<?php else: ?>
				<button id="btnRetry" onclick="window.location.reload();">Retry</button>

			<?php endif; ?>
			</section>
			

		<?php endif; ?>


		<footer>Group-Office &dash; Online Groupware Platform</footer>

	</body>
</html>

<?php
	ob_flush();
	flush();
	if($startSetup) {
		echo '<script type="text/javascript">
			clearInterval(loading);';
		$result = $setup->start() ;
	   if($result !== true){
			echo 'document.getElementById(\'process\').textContent = \'Installation failed:\ '. $result. '\';' ;
		} else {
			echo 'document.getElementById(\'process\').textContent = \'Installation complete\';
				document.getElementById(\'btnContinue\').style="";';
		}
		echo '</script>';
	} ?>