<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* database/qbe/ins_del_and_or_cell.twig */
class __TwigTemplate_725524dbe221db4986371ce7c8ac08571fe89e4cf9a770492f35fc866dba98ef extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<td class=\"value nowrap\">
  <table class=\"table table-borderless table-sm\">
    <tr>
      <td class=\"value nowrap p-0\">
        <small>";
        // line 5
        echo _gettext("Ins:");
        echo "</small>
        <input type=\"checkbox\" name=\"criteriaRowInsert[";
        // line 6
        echo twig_escape_filter($this->env, ($context["row_index"] ?? null), "html", null, true);
        echo "]\" aria-label=\"";
        echo _gettext("Insert");
        echo "\">
      </td>
      <td class=\"value p-0\">
        <strong>";
        // line 9
        echo _gettext("And:");
        echo "</strong>
      </td>
      <td class=\"p-0\">
        <input type=\"radio\" name=\"criteriaAndOrRow[";
        // line 12
        echo twig_escape_filter($this->env, ($context["row_index"] ?? null), "html", null, true);
        echo "]\" value=\"and\"";
        echo ((twig_get_attribute($this->env, $this->source, ($context["checked_options"] ?? null), "and", [], "any", false, false, false, 12)) ? (" checked") : (""));
        echo " aria-label=\"";
        echo _gettext("And");
        echo "\">
      </td>
    </tr>
    <tr>
      <td class=\"value nowrap p-0\">
        <small>";
        // line 17
        echo _gettext("Del:");
        echo "</small>
        <input type=\"checkbox\" name=\"criteriaRowDelete[";
        // line 18
        echo twig_escape_filter($this->env, ($context["row_index"] ?? null), "html", null, true);
        echo "]\" aria-label=\"";
        echo _gettext("Delete");
        echo "\">
      </td>
      <td class=\"value p-0\">
        <strong>";
        // line 21
        echo _gettext("Or:");
        echo "</strong>
      </td>
      <td class=\"p-0\">
        <input type=\"radio\" name=\"criteriaAndOrRow[";
        // line 24
        echo twig_escape_filter($this->env, ($context["row_index"] ?? null), "html", null, true);
        echo "]\" value=\"or\"";
        echo ((twig_get_attribute($this->env, $this->source, ($context["checked_options"] ?? null), "or", [], "any", false, false, false, 24)) ? (" checked") : (""));
        echo " aria-label=\"";
        echo _gettext("Or");
        echo "\">
      </td>
    </tr>
  </table>
</td>
";
    }

    public function getTemplateName()
    {
        return "database/qbe/ins_del_and_or_cell.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  91 => 24,  85 => 21,  77 => 18,  73 => 17,  61 => 12,  55 => 9,  47 => 6,  43 => 5,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "database/qbe/ins_del_and_or_cell.twig", "/home/apdwms/public_html/phpmyadmin/templates/database/qbe/ins_del_and_or_cell.twig");
    }
}
