<?php
/**
 * CssMin - A (simple) css minifier with benefits
 * 
 * --
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING 
 * BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND 
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, 
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * --
 * 
 * This {@link aCssMinifierFilter minifier filter} will parse the variable declarations out of @variables at-rule 
 * blocks. The variables will get store in the {@link CssVariablesMinifierPlugin} that will apply the variables to 
 * declaration.
 * --
 * 
 * @package		CssMin/Minifier/Filters
 * @link		http://code.google.com/p/cssmin/
 * @author		Joe Scylla <joe.scylla@gmail.com>
 * @copyright	2008 - 2011 Joe Scylla <joe.scylla@gmail.com>
 * @license		http://opensource.org/licenses/mit-license.php MIT License
 * @version		3.0.0
 */
class CssVariablesMinifierFilter extends aCssMinifierFilter
	{
	/**
	 * Implements {@link aCssMinifierFilter::filter()}.
	 * 
	 * @param array $tokens Array of objects of type aCssToken
	 * @return integer Count of added, changed or removed tokens; a return value large than 0 will rebuild the array
	 */
	public function apply(array &$tokens)
		{
		$variables			= array();
		$defaultMediaTypes	= array("all");
		$mediaTypes			= array();
		$remove				= array();
		for($i = 0, $l = count($tokens); $i < $l; $i++)
			{
			// @variables at-rule block found
			if (get_class($tokens[$i]) === "CssAtVariablesStartToken")
				{
				$remove[] = $i;
				$mediaTypes = (count($tokens[$i]->MediaTypes) == 0 ? $defaultMediaTypes : $tokens[$i]->MediaTypes);
				foreach ($mediaTypes as $mediaType)
					{
					if (!isset($variables[$mediaType]))
						{
						$variables[$mediaType] = array();
						}
					}
				// Read the variable declaration tokens
				for($i = $i; $i < $l; $i++)
					{
					// Found a variable declaration => read the variable values
					if (get_class($tokens[$i]) === "CssAtVariablesDeclarationToken")
						{
						foreach ($mediaTypes as $mediaType)
							{
							$variables[$mediaType][$tokens[$i]->Property] = $tokens[$i]->Value;
							}
						$remove[] = $i;
						}
					// Found the variables end token => break;
					elseif (get_class($tokens[$i]) === "CssAtVariablesEndToken")
						{
						$remove[] = $i;
						break;
						}
					}
				}
			}
		// Remove the complete @variables at-rule block
		foreach ($remove as $i)
			{
			$tokens[$i] = null;
			}
		if (!($plugin = $this->minifier->getPlugin("CssVariablesMinifierPlugin")))
			{
			new CssError("The plugin <code>CssVariablesMinifierPlugin</code> was not found but is required for <code>" . __CLASS__ . "</code>");
			}
		else
			{
			$plugin->setVariables($variables);
			}
		return count($remove);
		}
	}
?>