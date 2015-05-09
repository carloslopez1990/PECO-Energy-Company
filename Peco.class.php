<?php
# PECO Energy Company :: Energy Usage Data API
# @author: Carlos Ernesto López <celopez1990.blogspot.com>

class Peco {
	private $cookieTmp,
			$cookie, 
			$accountNumber, 
			$zipCode;

	public function __construct( $accountNumber, $zipCode ) {
		$this->accountNumber = $accountNumber;
		$this->zipCode = $zipCode;
	}

	public function run() {
		$this->sendRequest();
		$this->processCookie();
		return $this->getInfo();
	}

	private function sendRequest() {
		$cnx = fsockopen('ssl://peco.com', 443);

		$payload = '------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="__SPSCEditMenu"||true|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOWebPartPage_PostbackSource"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOTlPn_SelectedWpId"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOTlPn_View"||0|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOTlPn_ShowSettings"||False|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOGallery_SelectedLibrary"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOGallery_FilterString"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOTlPn_Button"||none|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="__EVENTTARGET"||ctl00$SPWebPartManager1$g_9b3a5413_7deb_4afe_94f5_533660179f31$ctl00$EnterBtn|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="__EVENTARGUMENT"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="__REQUESTDIGEST"||0xB860E6E23465BDC036AF01B2544A85C916E2B380A280B9D350F3F3890C0A192C8FD856D82EF2D0A07B99D907065CE7C269358F72103BB72210EE8CCF20C7CC19,26 Apr 2015 22:24:34 -0000|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOAuthoringConsole_FormContext"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOAC_EditDuringWorkflow"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOSPWebPartManager_DisplayModeName"||Browse|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOWebPartPage_Shared"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOLayout_LayoutChanges"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOLayout_InDesignMode"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOSPWebPartManager_OldDisplayModeName"||Browse|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="MSOSPWebPartManager_StartWebPartEditingName"||false|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="__VIEWSTATE"||/wEPDwUKMTYyMDE4Mjk0OA9kFgJmD2QWBAIED2QWBAIFDxYCHhNQcmV2aW91c0NvbnRyb2xNb2RlCymIAU1pY3Jvc29mdC5TaGFyZVBvaW50LldlYkNvbnRyb2xzLlNQQ29udHJvbE1vZGUsIE1pY3Jvc29mdC5TaGFyZVBvaW50LCBWZXJzaW9uPTEyLjAuMC4wLCBDdWx0dXJlPW5ldXRyYWwsIFB1YmxpY0tleVRva2VuPTcxZTliY2UxMTFlOTQyOWMBZAIID2QWAgICD2QWAgIBDxYCHwALKwQBZAIGD2QWCgICD2QWAgUmZ185YjNhNTQxM183ZGViXzRhZmVfOTRmNV81MzM2NjAxNzlmMzEPZBYCZg9kFgwCAQ8PFgIeB1Zpc2libGVoZGQCAw8PFgIfAWdkZAIFDw8WAh8BaGRkAgcPDxYCHwFoZBYCAhUPFgIeC18hSXRlbUNvdW50ZmQCCQ8PFgIfAWhkZAILDw8WAh8BaGRkAgYPZBYGAgEPDxYCHwFoZBYCAgIPDxYEHgRUZXh0BQdTaWduIEluHwFnFgIeBXN0eWxlBQ5kaXNwbGF5OmJsb2NrO2QCAw8PFgIfAWhkFgICAQ8WAh8BaBYCZg8WAh8BaBYEAgIPFgIfAWgWBgIBDxYCHwFoZAIDDxYCHwFoZAIFDxYCHwFoZAIDDw8WBB4JQWNjZXNzS2V5BQEvHwFoZGQCBQ8PFgIfAWhkFgQCAQ8PFgIfAWhkZAIDDw8WAh8BaGQWAgIBDw8WAh8BZ2QWBAIBDw8WAh8BaGQWHAIBDw8WAh8BaGRkAgMPFgIfAWhkAgUPDxYCHwFoZGQCBw8WAh8BaGQCCQ8PFgIfAWhkZAILDw8WAh8BaGRkAg0PDxYCHwFoZGQCDw8PFgQeB0VuYWJsZWRoHwFoZGQCEQ8PFgIfAWhkZAITDw8WBB8GaB8BaGRkAhUPDxYCHwFoZGQCFw8WAh8BaGQCGQ8WAh8BaGQCGw8PFgIfAWdkZAIDDw8WAh8BZ2QWBgIBDw8WAh8BZ2RkAgMPDxYCHwFnZGQCBQ8PFgIfAWdkZAIKD2QWBAIHDw8WBB8DZR8BaGRkAgkPDxYEHwNlHwFoZGQCFg9kFgICAQ9kFgICAw9kFgICAQ8WAh8ACysEAWQCGg9kFhACAQ8WAh8ACysEAWQCBQ9kFgICAQ8WAh8ACysEAWQCBw8WAh8ACysEAWQCCQ9kFgJmDw8WAh8BaGRkAgsPFgIfAAsrBAFkAg0PZBYCZg8PFgIfAWhkZAIPDxYCHwALKwQBZAIRD2QWAmYPDxYCHwFoZGQYAQUrY3RsMDAkUGxhY2VIb2xkZXJMZWZ0TmF2QmFyJExlZnROYXYkTGVmdE5hdg8PZAUUU21hbGwgQnVzaW5lc3NcVXNhZ2Vk11Uep0qywoRIvAw2ZVpfEKoIgaY=|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="__VIEWSTATEGENERATOR"||46481D46|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="__EVENTVALIDATION"||/wEWDgLpgtN1Asiby94BAoSQ4/kJAuO1xZMJAtP3neQNApK+0rwOAtLxhNoJAqTOr9oKAvzZkN8GAoyM+dYOArH//cINArXm8dAJAqSE2t4IAsCksBlY93iX/7kgrsDbgbpsboaejpA/BQ==|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="ctl00$GlobalSignIn$userNameInput"|| (EMAIL ADDRESS)|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="ctl00$GlobalSignIn$passwordInput"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="ctl00$SearchBox$searchInput"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="ctl00$EmailThisPage$yourName"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="ctl00$EmailThisPage$yourEmail"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="ctl00$EmailThisPage$friendName"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="ctl00$EmailThisPage$friendEmail"|||------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="ctl00$SPWebPartManager1$g_9b3a5413_7deb_4afe_94f5_533660179f31$ctl00$AccountTxt"||'.$this->accountNumber.'|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z|Content-Disposition: form-data; name="ctl00$SPWebPartManager1$g_9b3a5413_7deb_4afe_94f5_533660179f31$ctl00$ZipCodeTxt"||'.$this->zipCode.'|------WebKitFormBoundaryAc1S8AgCCE9gFF8Z--';
		$payload = str_replace( "|", "\n", $payload );

		$request = 
			"POST /myaccount/smallbusiness/pages/usage.aspx HTTP/1.1\r\n".
			"Host: peco.com\r\n".
			"Connection: keep-alive\r\n".
			"Content-Length: ".strlen( $payload )."\r\n".
			"Cache-Control: max-age=0\r\n".
			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n".
			"Origin: https://peco.com\r\n".
			"User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36\r\n".
			"Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryAc1S8AgCCE9gFF8Z\r\n".
			"Referer: https://peco.com/myaccount/smallbusiness/pages/usage.aspx\r\n".
			"Accept-Language: es-ES,es;q=0.8\r\n\r\n".$payload;

		fwrite( $cnx, $request );

		$final = '';
		while( !preg_match('</html>', $final) )
			$final .= fgets( $cnx );

		preg_match_all('/Set-Cookie:(?<cookie>\s{0,}.*)$/im', $final, $this->cookieTmp);

		fclose( $cnx );
	}

	private function processCookie() {
		$this->cookie = '';

		$this->cookieTmp = $this->cookieTmp[ 'cookie' ];

		foreach( $this->cookieTmp as $cookie )
			$this->cookie .= trim(str_replace(array("\r", "\n"), '', $cookie))."; ";
	}

	private function getInfo() {
		$payload = '------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="__SPSCEditMenu"||true|------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOWebPartPage_PostbackSource"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOTlPn_SelectedWpId"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOTlPn_View"||0|------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOTlPn_ShowSettings"||False|------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOGallery_SelectedLibrary"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOGallery_FilterString"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOTlPn_Button"||none|------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="__EVENTTARGET"||ctl00$SPWebPartManager1$g_9b3a5413_7deb_4afe_94f5_533660179f31$ctl00$SummaryDataBtn|------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="__EVENTARGUMENT"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="__REQUESTDIGEST"||0xEBE6C663D49D3CD481533A2FCD900742BAA6D37FFB800F8756CF256B4F53647B1133FC8DB1800C5FC786822DFA9A4EB8192D4399B402AE88E112B399F06418F8,26 Apr 2015 21:32:09 -0000|------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOAuthoringConsole_FormContext"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOAC_EditDuringWorkflow"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="__LASTFOCUS"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOSPWebPartManager_DisplayModeName"||Browse|------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOWebPartPage_Shared"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOLayout_LayoutChanges"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOLayout_InDesignMode"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOSPWebPartManager_OldDisplayModeName"||Browse|------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="MSOSPWebPartManager_StartWebPartEditingName"||false|------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="__VIEWSTATE"||/wEPDwUKMTYyMDE4Mjk0OA9kFgJmD2QWBAIED2QWBAIFDxYCHhNQcmV2aW91c0NvbnRyb2xNb2RlCymIAU1pY3Jvc29mdC5TaGFyZVBvaW50LldlYkNvbnRyb2xzLlNQQ29udHJvbE1vZGUsIE1pY3Jvc29mdC5TaGFyZVBvaW50LCBWZXJzaW9uPTEyLjAuMC4wLCBDdWx0dXJlPW5ldXRyYWwsIFB1YmxpY0tleVRva2VuPTcxZTliY2UxMTFlOTQyOWMBZAIID2QWAgICD2QWAgIBDxYCHwALKwQBZAIGD2QWCgICD2QWAgUmZ185YjNhNTQxM183ZGViXzRhZmVfOTRmNV81MzM2NjAxNzlmMzEPZBYCZg8PFgIeEUlzU3VtbWFyeURhdGFPbmx5Z2QWDAIBDw8WAh4HVmlzaWJsZWhkZAIDDw8WAh8CaGQWCAILDw8WAh4MRXJyb3JNZXNzYWdlBSsgaXMgYSByZXF1aXJlZCBmaWVsZC4gUGxlYXNlIGVudGVyIGEgdmFsdWUuZGQCDQ8PFgQfAwU9IHNob3VsZCBjb250YWluIDEwIG51bWVyaWMgY2hhcmFjdGVycyBvbmx5IChpLmUuIDEyMzQ1Njc4OTApLh4UVmFsaWRhdGlvbkV4cHJlc3Npb24FC15bMC05XXsxMH0kZGQCFQ8PFgIfAwUrIGlzIGEgcmVxdWlyZWQgZmllbGQuIFBsZWFzZSBlbnRlciBhIHZhbHVlLmRkAhcPDxYEHwMFLlBsZWFzZSBlbnRlciBhIHZhbGlkIGZvcm1hdCBmb3IgIChpLmUuIDEyMzQ1KS4fBAUKXlswLTldezV9JGRkAgUPDxYCHwJnZBYGAgEPDxYCHwJoZGQCAw8WAh4Fc3R5bGUFDWRpc3BsYXk6YmxvY2tkAgUPFgIfBQUNZGlzcGxheTpibG9ja2QCBw8PFgIfAmhkFgICFQ8WAh4LXyFJdGVtQ291bnRmZAIJDw8WAh8CaGRkAgsPDxYCHwJoZGQCBg9kFgYCAQ8PFgIfAmhkFgICAg8PFgQeBFRleHQFB1NpZ24gSW4fAmcWAh8FBQ5kaXNwbGF5OmJsb2NrO2QCAw8PFgIfAmhkFgICAQ8WAh8CaBYCZg8WAh8CaBYEAgIPFgIfAmgWBgIBDxYCHwJoZAIDDxYCHwJoZAIFDxYCHwJoZAIDDw8WBB4JQWNjZXNzS2V5BQEvHwJoZGQCBQ8PFgIfAmhkFgQCAQ8PFgIfAmhkZAIDDw8WAh8CaGQWAgIBDw8WAh8CZ2QWBAIBDw8WAh8CaGQWHAIBDw8WAh8CaGRkAgMPFgIfAmhkAgUPDxYCHwJoZGQCBw8WAh8CaGQCCQ8PFgIfAmhkZAILDw8WAh8CaGRkAg0PDxYCHwJoZGQCDw8PFgQeB0VuYWJsZWRoHwJoZGQCEQ8PFgIfAmhkZAITDw8WBB8JaB8CaGRkAhUPDxYCHwJoZGQCFw8WAh8CaGQCGQ8WAh8CaGQCGw8PFgIfAmdkZAIDDw8WAh8CZ2QWBgIBDw8WAh8CZ2RkAgMPDxYCHwJnZGQCBQ8PFgIfAmdkZAIKD2QWBAIHDw8WBB8HZR8CaGRkAgkPDxYEHwdlHwJoZGQCFg9kFgICAQ9kFgICAw9kFgICAQ8WAh8ACysEAWQCGg9kFhACAQ8WAh8ACysEAWQCBQ9kFgICAQ8WAh8ACysEAWQCBw8WAh8ACysEAWQCCQ9kFgJmDw8WAh8CaGRkAgsPFgIfAAsrBAFkAg0PZBYCZg8PFgIfAmhkZAIPDxYCHwALKwQBZAIRD2QWAmYPDxYCHwJoZGQYAQUrY3RsMDAkUGxhY2VIb2xkZXJMZWZ0TmF2QmFyJExlZnROYXYkTGVmdE5hdg8PZAUUU21hbGwgQnVzaW5lc3NcVXNhZ2VkGdx/RLQg8kehgMbJrFEAQbbOp8U=|------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="__VIEWSTATEGENERATOR"||46481D46|------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="__EVENTVALIDATION"||/wEWDQKgtOLADQLIm8veAQKEkOP5CQLjtcWTCQLT953kDQKSvtK8DgLS8YTaCQKkzq/aCgL82ZDfBgKMjPnWDgKx//3CDQKrpOaUBALJk8jsDbb7Y60Zd8MyhM8K2ypvMC0iNx+C|------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="ctl00$GlobalSignIn$userNameInput"|| (EMAIL ADDRESS)|------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="ctl00$GlobalSignIn$passwordInput"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="ctl00$SearchBox$searchInput"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="ctl00$EmailThisPage$yourName"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="ctl00$EmailThisPage$yourEmail"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="ctl00$EmailThisPage$friendName"|||------WebKitFormBoundarydjKOdW2ld41R9o3d|Content-Disposition: form-data; name="ctl00$EmailThisPage$friendEmail"|||------WebKitFormBoundarydjKOdW2ld41R9o3d--';
		$payload = str_replace( "|", "\n", $payload );

		$request = 
			"POST /myaccount/smallbusiness/pages/usage.aspx HTTP/1.1\r\n".
			"Host: peco.com\r\n".
			"Connection: keep-alive\r\n".
			"Content-Length: ".strlen( $payload )."\r\n".
			"Cache-Control: max-age=0\r\n".
			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n".
			"Origin: https://peco.com\r\n".
			"User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36\r\n".
			"Content-Type: multipart/form-data; boundary=----WebKitFormBoundarydjKOdW2ld41R9o3d\r\n".
			"Referer: https://peco.com/myaccount/smallbusiness/pages/usage.aspx\r\n".
			"Accept-Language: es-ES,es;q=0.8\r\n".
			"Cookie: ".$this->cookie."\r\n\r\n".$payload;

		$cnx = fsockopen('ssl://peco.com', 443);

		fwrite( $cnx, $request );

		$info = '';
		while( !preg_match('</body>', $info) )
			$info .= fgets( $cnx );

		$final = array();

		# current_capacty_plc
			$_info = $this->between($info, 'id="ctl00_ContentPlaceHolder1_currentPlc"', '</table>');

			preg_match_all('|\d+\.\d+|', $_info, $matches);
			$final['current_capacty_plc']['value'] = $matches[0][0];

			preg_match_all('|\d{2}-\d{2}-\d{4}|', $_info, $matches);
			$final['current_capacty_plc']['st-art_data'] = $matches[0][0];
			$final['current_capacty_plc']['end_date'] = $matches[0][1];

		# nspl_kw
			$_info = $this->between($info, 'id="ctl00_ContentPlaceHolder1_networkPlc"', '</table>');

			preg_match_all('|\d+\.\d+|', $_info, $matches);
			$final['nspl_kw']['value'] = $matches[0][0];

			preg_match_all('|\d{2}-\d{2}-\d{4}|', $_info, $matches);
			$final['nspl_kw']['start_data'] = $matches[0][0];
			$final['nspl_kw']['end_date'] = $matches[0][1];

		# pending_capacity_plc_kw
			$_info = $this->between($info, 'id="ctl00_ContentPlaceHolder1_pendingPlc"', '</table>');

			preg_match_all('|\d+\.\d+|', $_info, $matches);
			$final['pending_capacity_plc_kw']['value'] = $matches[0][0];

			preg_match_all('|\d{2}-\d{2}-\d{4}|', $_info, $matches);
			$final['pending_capacity_plc_kw']['start_data'] = $matches[0][0];
			$final['pending_capacity_plc_kw']['end_date'] = $matches[0][1];

		# usage data 
			$_info = $this->between($info, 'id="ctl00_ContentPlaceHolder1_usageData"', '</table>');

			$_info = str_replace( array( "\n", "\r", "\t", " ", "<td>", "<tr>" ), "", $_info );

			$_info = explode('</tr>', $_info);
			unset($_info[0]);

			$fields = explode(',', 'rate_code,rate_class,strata,start_date,end_date,usage_kwh,demand_kw');

			foreach( $_info as $line ) {
				$cols = explode('</td>', $line);
				unset( $cols[ count( $cols ) - 1 ] );
				$tmp = array();
				$col_counter = 0;
				foreach( $cols as $col ) 
					$tmp[ $fields[ $col_counter++ ] ] = $col;

				$final['usage_data'][] = $tmp;
			}

			unset( $final['usage_data'][ count($final['usage_data']) - 1 ] );
		# --

		fclose( $cnx );

		return json_encode( $final );
	}

	private function between( $info, $start, $end ) {
		$inf = explode( $start, $info );
		$inf = $inf[1];
		$inf = explode( $end, $inf );
		return $inf[0];
	}
}
