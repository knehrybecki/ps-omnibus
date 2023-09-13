$(document).ready(function () {
	const OmnibusInfo = () => {
		if ($('.omnibus-info').length === 0) {
			return
		}
		document
			.querySelector('.omnibus-info')
			.addEventListener('click', () => [
				(document.querySelector('#modal-popup-omnibus').style.display = 'block'),
				(document.querySelector('#modal-popup-omnibus').style.opacity = '1'),
			])

		document
			.querySelector('.popup-close-omnibus')
			.addEventListener('click', () => [
				(document.querySelector('#modal-popup-omnibus').style.display = 'none'),
				(document.querySelector('#modal-popup-omnibus').style.opacity = '0'),
			])
		// }
	}

	OmnibusInfo()
	prestashop.on('updatedProduct', data => {
		OmnibusInfo()
	})
})
